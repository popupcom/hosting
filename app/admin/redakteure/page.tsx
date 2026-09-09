import { requireSuperadmin } from "@/lib/auth";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { fmtDate } from "@/lib/format";
import AdminNav from "@/components/AdminNav";
import { inviteEditorAction, toggleEditorAction } from "../actions";

export const dynamic = "force-dynamic";

export default async function Redakteure() {
  const profile = await requireSuperadmin();
  const { data: rows } = await supabaseAdmin()
    .from("profiles").select("*").order("created_at");

  return (
    <div className="wrap">
      <AdminNav profile={profile} />
      <h2 style={{ color: "var(--c-head)", fontSize: "1.5rem", fontWeight: 600, marginBottom: "1rem" }}>
        Redakteursverwaltung
      </h2>

      <section className="card" style={{ padding: "1.4rem 1.6rem", marginBottom: "1.4rem" }}>
        <h3 style={{ fontSize: ".85rem", letterSpacing: ".08em", textTransform: "uppercase", color: "var(--c-muted)", marginBottom: ".8rem" }}>
          Redakteur:in einladen
        </h3>
        <form action={inviteEditorAction} className="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
          <div className="field"><label>Name</label><input name="name" required /></div>
          <div className="field"><label>E-Mail</label><input name="email" type="email" required /></div>
          <div className="field">
            <label>Rolle</label>
            <select name="role" defaultValue="redakteur">
              <option value="redakteur">Redakteur:in</option>
              <option value="superadmin">Super-Admin</option>
            </select>
          </div>
          <button className="btn btn-primary" type="submit">Einladung senden</button>
        </form>
        <p style={{ fontSize: ".82rem", color: "var(--c-muted)", marginTop: ".6rem" }}>
          Die Einladung kommt per E-Mail von Supabase; beim ersten Öffnen wird das eigene Passwort gesetzt.
        </p>
      </section>

      <div style={{ overflowX: "auto" }}>
        <table className="list">
          <thead><tr><th>Name</th><th>E-Mail</th><th>Rolle</th><th>Seit</th><th>Status</th><th></th></tr></thead>
          <tbody>
            {(rows ?? []).map((r) => (
              <tr key={r.user_id}>
                <td><b>{r.name || "–"}</b></td>
                <td>{r.email}</td>
                <td>{r.role === "superadmin" ? <span className="badge b-freigegeben">Super-Admin</span> : "Redakteur:in"}</td>
                <td style={{ fontSize: ".88rem" }}>{fmtDate(r.created_at)}</td>
                <td>{r.aktiv ? <span className="badge b-neu">aktiv</span> : <span className="badge b-rueckfrage">deaktiviert</span>}</td>
                <td>
                  {r.user_id !== profile.user_id && (
                    <form action={toggleEditorAction.bind(null, r.user_id, !r.aktiv)}>
                      <button className="btn btn-ghost btn-sm" type="submit">
                        {r.aktiv ? "Deaktivieren" : "Aktivieren"}
                      </button>
                    </form>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
