import Link from "next/link";
import type { Profile } from "@/lib/auth";
import { signOutAction } from "@/app/admin/actions";

export default function AdminNav({ profile }: { profile: Profile }) {
  return (
    <div style={{ display: "flex", alignItems: "center", gap: "1rem", flexWrap: "wrap", margin: "1.6rem 0" }}>
      <b style={{ color: "var(--c-head)", fontSize: "1.2rem" }}>Redaktion</b>
      <Link href="/admin" className="btn btn-ghost btn-sm">Eingänge</Link>
      {profile.role === "superadmin" && (
        <Link href="/admin/redakteure" className="btn btn-ghost btn-sm">Redakteure</Link>
      )}
      <span style={{ marginLeft: "auto", fontSize: ".9rem", color: "var(--c-muted)" }}>
        {profile.name || profile.email}
        {profile.role === "superadmin" && <span className="badge b-freigegeben" style={{ marginLeft: ".5rem" }}>Super-Admin</span>}
      </span>
      <form action={signOutAction}>
        <button className="btn btn-ghost btn-sm" type="submit">Abmelden</button>
      </form>
    </div>
  );
}
