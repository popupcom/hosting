import Link from "next/link";
import { notFound } from "next/navigation";
import { requireEditor } from "@/lib/auth";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { absenderName, fmtDate, fmtDateTime, KATEGORIEN, STATUS_LABEL } from "@/lib/format";
import AdminNav from "@/components/AdminNav";
import {
  assignToMeAction, manualApproveAction, notizAction, remindApprovalAction,
  rueckfrageAction, sendApprovalAction, setStatusAction,
} from "../../actions";

export const dynamic = "force-dynamic";

export default async function Detail({ params }: { params: Promise<{ id: string }> }) {
  const profile = await requireEditor();
  const { id } = await params;
  const db = supabaseAdmin();

  const { data: s } = await db.from("submissions")
    .select("*, issue:issues(label,erscheint_am), profil:profiles!submissions_assigned_to_fkey(name,email)")
    .eq("id", id).single();
  if (!s) notFound();

  const [{ data: photos }, { data: events }, { data: approvals }] = await Promise.all([
    db.from("submission_photos").select("*").eq("submission_id", id).order("created_at"),
    db.from("submission_events").select("*").eq("submission_id", id).order("created_at", { ascending: false }),
    db.from("approvals").select("*").eq("submission_id", id).order("runde", { ascending: false }),
  ]);

  const photoLinks = photos?.length
    ? (await db.storage.from("photos").createSignedUrls(photos.map((p) => p.storage_path), 3600)).data
    : [];
  const offeneFreigabe = approvals?.find((a) => a.entscheidung === "offen");
  const bearbeiter = Array.isArray(s.profil) ? s.profil[0] : s.profil;
  const issue = Array.isArray(s.issue) ? s.issue[0] : s.issue;

  return (
    <div className="wrap">
      <AdminNav profile={profile} />
      <div style={{ display: "flex", gap: "1rem", alignItems: "flex-start", flexWrap: "wrap", marginBottom: "1.2rem" }}>
        <Link href="/admin" className="btn btn-ghost btn-sm">←&ensp;Zur Übersicht</Link>
        <div style={{ flex: 1 }}>
          <h2 style={{ color: "var(--c-head)", fontSize: "1.5rem", fontWeight: 600 }}>{s.titel}</h2>
          <div style={{ color: "var(--c-muted)", fontSize: ".88rem" }}>
            {s.ref} · {absenderName(s)} · Ausgabe {issue?.label ?? "–"} · eingereicht am {fmtDateTime(s.created_at)}
            {s.korrekturschleifen > 0 && <> · {s.korrekturschleifen} Korrekturschleife{s.korrekturschleifen > 1 ? "n" : ""}</>}
          </div>
        </div>
        <span className={`badge b-${s.status}`}>{STATUS_LABEL[s.status]}</span>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
        <div className="lg:col-span-2 flex flex-col gap-5">
          <section className="card" style={{ padding: "1.4rem 1.6rem" }}>
            <h3 style={{ fontSize: ".85rem", letterSpacing: ".08em", textTransform: "uppercase", color: "var(--c-muted)", marginBottom: ".8rem" }}>Beitragstext</h3>
            {s.einleitung && <p style={{ fontWeight: 600, fontStyle: "italic", color: "var(--c-head)", marginBottom: ".8rem" }}>{s.einleitung}</p>}
            {(s.text ?? "").split(/\n{2,}/).map((p: string, i: number) => <p key={i} style={{ marginBottom: ".7rem" }}>{p}</p>)}
            {s.credits && <p style={{ marginTop: "1rem", paddingTop: ".8rem", borderTop: "1px dashed var(--c-line)", fontSize: ".88rem", color: "var(--c-muted)" }}>{s.credits}</p>}
          </section>

          <section className="card" style={{ padding: "1.4rem 1.6rem" }}>
            <h3 style={{ fontSize: ".85rem", letterSpacing: ".08em", textTransform: "uppercase", color: "var(--c-muted)", marginBottom: ".8rem" }}>
              Fotos ({photos?.length ?? 0})
            </h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
              {(photos ?? []).map((p, i) => (
                <a key={p.id} className="card" style={{ padding: ".5rem", fontSize: ".78rem", textDecoration: "none", color: "inherit" }}
                  href={photoLinks?.[i]?.signedUrl ?? "#"} target="_blank" rel="noreferrer">
                  <b style={{ display: "block", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>{p.original_name}</b>
                  <span style={{ color: "var(--c-muted)" }}>
                    {p.width?.toLocaleString("de-AT")} × {p.height?.toLocaleString("de-AT")} px ·{" "}
                    {((p.bytes ?? 0) / 1048576).toLocaleString("de-AT", { maximumFractionDigits: 1 })} MB
                    {!p.uploaded && <> · <span style={{ color: "var(--c-orange)", fontWeight: 700 }}>Upload ausständig</span></>}
                  </span>
                  <span style={{ display: "block", color: "var(--c-blue)", fontWeight: 700 }}>Original öffnen ↗</span>
                </a>
              ))}
              {(photos ?? []).length === 0 && <p style={{ color: "var(--c-muted)", fontSize: ".9rem" }}>Keine Fotos hochgeladen.</p>}
            </div>
          </section>

          <section className="card" style={{ padding: "1.4rem 1.6rem" }}>
            <h3 style={{ fontSize: ".85rem", letterSpacing: ".08em", textTransform: "uppercase", color: "var(--c-muted)", marginBottom: ".8rem" }}>Druckfreigabe</h3>
            {offeneFreigabe ? (
              <div>
                <p style={{ fontSize: ".93rem" }}>
                  <b>{offeneFreigabe.pdf_name}</b> (Runde {offeneFreigabe.runde})<br />
                  gesendet am {fmtDateTime(offeneFreigabe.gesendet_am)} an {offeneFreigabe.gesendet_an}<br />
                  Frist: <b>{fmtDate(offeneFreigabe.frist)}</b> – danach automatische Freigabe
                </p>
                <div style={{ display: "flex", gap: ".6rem", marginTop: ".8rem", flexWrap: "wrap" }}>
                  <form action={remindApprovalAction.bind(null, s.id)}>
                    <button className="btn btn-ghost btn-sm" type="submit">Erinnerung senden</button>
                  </form>
                  <form action={manualApproveAction.bind(null, s.id)}>
                    <button className="btn btn-ghost btn-sm" type="submit">Manuell freigeben</button>
                  </form>
                </div>
              </div>
            ) : (
              <form action={sendApprovalAction.bind(null, s.id)} style={{ display: "flex", flexDirection: "column", gap: ".8rem" }}>
                <p style={{ fontSize: ".9rem", color: "var(--c-muted)" }}>
                  Gestaltetes PDF hochladen und zur Freigabe an {s.email} senden.
                  {s.status === "pdf_aenderung" && <b style={{ color: "var(--c-orange)" }}> Änderungswunsch liegt vor – korrigierte Version senden.</b>}
                </p>
                <input type="file" name="pdf" accept="application/pdf" required />
                <label style={{ fontSize: ".9rem" }}>
                  Frist:{" "}
                  <select name="tage" defaultValue="7" style={{ border: "1px solid #c6c6c6", padding: ".3rem" }}>
                    <option value="3">3</option><option value="5">5</option>
                    <option value="7">7</option><option value="14">14</option>
                  </select>{" "}Tage (danach automatische Freigabe)
                </label>
                <button className="btn btn-primary btn-sm" type="submit" style={{ alignSelf: "flex-start" }}>
                  Zur Freigabe senden
                </button>
              </form>
            )}
            {(approvals ?? []).filter((a) => a.entscheidung !== "offen").map((a) => (
              <p key={a.id} style={{ fontSize: ".85rem", color: "var(--c-muted)", marginTop: ".6rem" }}>
                Runde {a.runde}: {a.entscheidung === "aenderung"
                  ? `Änderungswunsch am ${fmtDateTime(a.entschieden_am)} – „${a.aenderungswunsch}“`
                  : `${a.entscheidung === "auto" ? "Automatisch freigegeben" : "Freigegeben"} am ${fmtDateTime(a.entschieden_am)}`}
              </p>
            ))}
          </section>
        </div>

        <div className="flex flex-col gap-5">
          <section className="card" style={{ padding: "1.4rem 1.6rem" }}>
            <h3 style={{ fontSize: ".85rem", letterSpacing: ".08em", textTransform: "uppercase", color: "var(--c-muted)", marginBottom: ".8rem" }}>Einsender:in</h3>
            <p style={{ fontSize: ".93rem" }}>
              <b>Kategorie:</b> {KATEGORIEN[s.kategorie as keyof typeof KATEGORIEN] ?? "–"}<br />
              {s.organisation && <><b>Organisation:</b> {s.organisation}<br /></>}
              <b>Name:</b> {s.vorname} {s.nachname}{s.funktion ? ` (${s.funktion})` : ""}<br />
              <b>E-Mail:</b> <a href={`mailto:${s.email}`} style={{ color: "var(--c-blue)" }}>{s.email}</a><br />
              {s.telefon && <><b>Telefon:</b> {s.telefon}</>}
            </p>
          </section>

          <section className="card" style={{ padding: "1.4rem 1.6rem" }}>
            <h3 style={{ fontSize: ".85rem", letterSpacing: ".08em", textTransform: "uppercase", color: "var(--c-muted)", marginBottom: ".8rem" }}>Bearbeitung</h3>
            {s.assigned_to ? (
              <p style={{ fontSize: ".93rem" }}>
                Zugewiesen an <b>{bearbeiter?.name || bearbeiter?.email}</b><br />
                <span style={{ color: "var(--c-muted)" }}>seit {fmtDateTime(s.assigned_at)}</span>
              </p>
            ) : (
              <form action={assignToMeAction.bind(null, s.id)}>
                <button className="btn btn-primary btn-sm" type="submit">Auftrag übernehmen</button>
              </form>
            )}
            <div style={{ display: "flex", gap: ".4rem", flexWrap: "wrap", marginTop: ".9rem" }}>
              {["neu", "pruefung", "rueckfrage", "freigegeben"].map((st) => (
                <form key={st} action={setStatusAction.bind(null, s.id, st)}>
                  <button className={`btn btn-sm ${s.status === st ? "btn-primary" : "btn-ghost"}`} type="submit">
                    {STATUS_LABEL[st]}
                  </button>
                </form>
              ))}
            </div>
          </section>

          <section className="card" style={{ padding: "1.4rem 1.6rem" }}>
            <h3 style={{ fontSize: ".85rem", letterSpacing: ".08em", textTransform: "uppercase", color: "var(--c-muted)", marginBottom: ".8rem" }}>Rückfrage / Notiz</h3>
            <form action={rueckfrageAction.bind(null, s.id)} style={{ display: "flex", flexDirection: "column", gap: ".6rem" }}>
              <textarea name="text" rows={3} placeholder="Rückfrage an die Ansprechperson … (per E-Mail)"
                style={{ border: "1px solid #c6c6c6", padding: ".6rem .8rem", width: "100%" }} />
              <button className="btn btn-primary btn-sm" type="submit" style={{ alignSelf: "flex-start" }}>Rückfrage senden</button>
            </form>
            <form action={notizAction.bind(null, s.id)} style={{ display: "flex", flexDirection: "column", gap: ".6rem", marginTop: ".8rem" }}>
              <textarea name="text" rows={2} placeholder="Interne Notiz …"
                style={{ border: "1px solid #c6c6c6", padding: ".6rem .8rem", width: "100%" }} />
              <button className="btn btn-ghost btn-sm" type="submit" style={{ alignSelf: "flex-start" }}>Notiz speichern</button>
            </form>
          </section>

          <section className="card" style={{ padding: "1.4rem 1.6rem" }}>
            <h3 style={{ fontSize: ".85rem", letterSpacing: ".08em", textTransform: "uppercase", color: "var(--c-muted)", marginBottom: ".8rem" }}>Verlauf</h3>
            <ul className="timeline">
              {(events ?? []).map((e) => (
                <li key={e.id} className={`t-${e.typ}`}>
                  <b>{e.akteur}</b> <span style={{ color: "var(--c-muted)", fontSize: ".78rem" }}>· {fmtDateTime(e.created_at)}</span>
                  <br />{e.text}
                </li>
              ))}
            </ul>
          </section>
        </div>
      </div>
    </div>
  );
}
