import Link from "next/link";
import { requireEditor } from "@/lib/auth";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { absenderName, fmtDateTime, STATUS_LABEL } from "@/lib/format";
import AdminNav from "@/components/AdminNav";
import { assignToMeAction } from "./actions";

export const dynamic = "force-dynamic";

export default async function AdminList({
  searchParams,
}: {
  searchParams: Promise<{ status?: string; zu?: string }>;
}) {
  const profile = await requireEditor();
  const { status, zu } = await searchParams;
  const db = supabaseAdmin();

  let query = db.from("submissions")
    .select("id,ref,titel,organisation,vorname,nachname,rubrik,status,created_at,assigned_to,profil:profiles!submissions_assigned_to_fkey(name,email)")
    .order("created_at", { ascending: false });
  if (status) query = query.eq("status", status);
  if (zu === "mir") query = query.eq("assigned_to", profile.user_id);
  if (zu === "offen") query = query.is("assigned_to", null);
  const { data: rows } = await query;

  const { data: alle } = await db.from("submissions").select("status,assigned_to");
  const cnt = (s: string) => (alle ?? []).filter((x) => x.status === s).length;
  const offen = (alle ?? []).filter((x) => !x.assigned_to && ["neu", "pruefung", "rueckfrage"].includes(x.status)).length;

  return (
    <div className="wrap">
      <AdminNav profile={profile} />

      <div className="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
        {[
          ["Gesamt", (alle ?? []).length],
          ["Nicht zugewiesen", offen],
          ["In Prüfung / Rückfrage", cnt("pruefung") + cnt("rueckfrage")],
          ["PDF-Freigabe läuft", cnt("pdf_offen")],
          ["PDF freigegeben", cnt("pdf_ok")],
        ].map(([l, v]) => (
          <div key={String(l)} className="card" style={{ padding: "1rem 1.2rem" }}>
            <div style={{ fontSize: "1.6rem", fontWeight: 700, color: "var(--c-blue)" }}>{v}</div>
            <div style={{ fontSize: ".85rem", color: "var(--c-muted)" }}>{l}</div>
          </div>
        ))}
      </div>

      <div style={{ display: "flex", gap: ".6rem", flexWrap: "wrap", marginBottom: "1rem" }}>
        <Link href="/admin" className="btn btn-ghost btn-sm">Alle</Link>
        <Link href="/admin?zu=offen" className="btn btn-ghost btn-sm">Offene Eingänge</Link>
        <Link href="/admin?zu=mir" className="btn btn-ghost btn-sm">Meine Aufträge</Link>
        {Object.entries(STATUS_LABEL).map(([k, v]) => (
          <Link key={k} href={`/admin?status=${k}`} className="btn btn-ghost btn-sm">{v}</Link>
        ))}
      </div>

      <div style={{ overflowX: "auto" }}>
        <table className="list">
          <thead><tr><th>Ref.</th><th>Absender / Titel</th><th>Eingereicht</th><th>Status</th><th>Bearbeitung</th><th></th></tr></thead>
          <tbody>
            {(rows ?? []).map((r) => {
              const p = Array.isArray(r.profil) ? r.profil[0] : r.profil;
              return (
                <tr key={r.id}>
                  <td style={{ color: "var(--c-muted)", fontSize: ".88rem" }}>{r.ref}</td>
                  <td>
                    <b style={{ color: "var(--c-head)" }}>{absenderName(r)}</b><br />
                    <span style={{ fontSize: ".8rem", color: "var(--c-muted)" }}>{r.rubrik ? `${r.rubrik} · ` : ""}{r.titel}</span>
                  </td>
                  <td style={{ fontSize: ".88rem" }}>{fmtDateTime(r.created_at)}</td>
                  <td><span className={`badge b-${r.status}`}>{STATUS_LABEL[r.status]}</span></td>
                  <td style={{ fontSize: ".88rem" }}>
                    {r.assigned_to ? (p?.name || p?.email || "zugewiesen") : (
                      <form action={assignToMeAction.bind(null, r.id)}>
                        <button className="btn btn-outline btn-sm" type="submit">Übernehmen</button>
                      </form>
                    )}
                  </td>
                  <td><Link href={`/admin/beitrag/${r.id}`} style={{ color: "var(--c-blue)", fontWeight: 700 }}>Öffnen →</Link></td>
                </tr>
              );
            })}
            {(rows ?? []).length === 0 && (
              <tr><td colSpan={6} style={{ textAlign: "center", padding: "2rem", color: "var(--c-muted)" }}>Keine Einreichungen gefunden.</td></tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}
