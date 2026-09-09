import { notFound } from "next/navigation";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { verifyApprovalToken } from "@/lib/tokens";
import { absenderName, fmtDate, fmtDateTime } from "@/lib/format";
import { kundeAenderungAction, kundeFreigebenAction } from "../actions";

export const dynamic = "force-dynamic";

export default async function FreigabePage({ params }: { params: Promise<{ token: string }> }) {
  const { token } = await params;
  const approvalId = verifyApprovalToken(token);
  if (!approvalId) notFound();

  const db = supabaseAdmin();
  const { data: a } = await db.from("approvals")
    .select("*, submission:submissions(*, issue:issues(label,erscheint_am), profil:profiles!submissions_assigned_to_fkey(name))")
    .eq("id", approvalId).single();
  if (!a) notFound();
  const s = Array.isArray(a.submission) ? a.submission[0] : a.submission;
  const issue = Array.isArray(s.issue) ? s.issue[0] : s.issue;
  const bearbeiter = Array.isArray(s.profil) ? s.profil[0] : s.profil;

  const { data: pdfUrl } = await db.storage.from("approvals")
    .createSignedUrl(a.pdf_storage_path, 3600);

  const restTage = Math.max(0, Math.ceil((new Date(a.frist).getTime() - Date.now()) / 86400000));

  return (
    <div className="wrap" style={{ maxWidth: 860 }}>
      <h1 className="sechead">Druckfreigabe</h1>
      <div className="secline" />

      <section className="card" style={{ padding: "2rem" }}>
        <h2 style={{ color: "var(--c-head)", fontSize: "1.4rem", fontWeight: 600 }}>
          Freigabe Ihrer Seite in der Gemeindezeitung
        </h2>
        <p style={{ color: "var(--c-muted)", margin: ".5rem 0 1.2rem" }}>
          Grüß Gott, {s.vorname} {s.nachname}! Der Beitrag „{s.titel}“ ({absenderName(s)}, {s.ref})
          wurde für die Ausgabe <b>{issue?.label}</b> gestaltet. Bitte prüfen Sie die Vorschau.
        </p>

        <div className="infobox" style={{ marginBottom: "1.2rem" }}>
          <div style={{ fontSize: ".92rem" }}>
            <b>Zusammenfassung:</b><br />
            Erscheint / online: <b>{issue?.erscheint_am ? fmtDate(issue.erscheint_am) : `Ausgabe ${issue?.label}`}</b><br />
            Bearbeitet von: <b>{bearbeiter?.name ?? "Redaktionsteam"}</b>
            {s.assigned_at && <> (übernommen am {fmtDateTime(s.assigned_at)})</>}<br />
            Korrekturschleifen bisher: <b>{s.korrekturschleifen}</b> · aktuelle Runde: <b>{a.runde}</b>
          </div>
        </div>

        <p style={{ marginBottom: "1.2rem" }}>
          <a className="btn btn-primary" href={pdfUrl?.signedUrl ?? "#"} target="_blank" rel="noreferrer">
            PDF ansehen: {a.pdf_name}
          </a>
        </p>

        {a.entscheidung === "offen" ? (
          <>
            <div className="fristbox" style={{ marginBottom: "1.2rem" }}>
              <span>
                Bitte um Rückmeldung bis <b>{fmtDate(a.frist)}</b> (noch {restTage} Tage).
                Erfolgt bis dahin keine Rückmeldung, gilt die Seite automatisch als freigegeben.
              </span>
            </div>
            <form action={kundeFreigebenAction.bind(null, token)} style={{ marginBottom: "1.6rem" }}>
              <button className="btn btn-primary" type="submit">Seite freigeben&ensp;✓</button>
            </form>
            <form action={kundeAenderungAction.bind(null, token)} style={{ display: "flex", flexDirection: "column", gap: ".6rem" }}>
              <label style={{ fontWeight: 700, fontSize: ".93rem" }}>Oder: Änderungswunsch mitteilen</label>
              <textarea name="text" rows={4} required
                placeholder="Was sollen wir ändern? (z. B. Tippfehler, andere Bildauswahl …)"
                style={{ border: "1px solid #c6c6c6", padding: ".6rem .8rem" }} />
              <button className="btn btn-ghost" type="submit" style={{ alignSelf: "flex-start" }}>
                Änderungswunsch senden
              </button>
            </form>
          </>
        ) : a.entscheidung === "aenderung" ? (
          <p className="card" style={{ padding: "1rem 1.2rem", background: "var(--c-orange-soft)", borderColor: "#ecd0a4", color: "var(--c-orange)", fontWeight: 600 }}>
            Ihr Änderungswunsch wurde an die Redaktion übermittelt. Sie erhalten eine neue Version zur Freigabe.
          </p>
        ) : (
          <p className="card" style={{ padding: "1rem 1.2rem", background: "var(--c-green-soft)", borderColor: "#bfe0cc", color: "var(--c-green)", fontWeight: 600 }}>
            Vielen Dank! Die Seite wurde {a.entscheidung === "auto" ? "nach Fristablauf automatisch" : ""} freigegeben
            {a.entschieden_am && <> ({fmtDateTime(a.entschieden_am)})</>}.
          </p>
        )}
      </section>
    </div>
  );
}
