import Link from "next/link";
import { headers } from "next/headers";

// Hinweis: Der Auth-Schutz liegt in middleware.ts (Session) und in den
// Server-Actions/Pages (Rollenprüfung via requireEditor/requireSuperadmin).
export default async function AdminLayout({ children }: { children: React.ReactNode }) {
  await headers(); // erzwingt dynamisches Rendering für den gesamten Admin-Bereich
  return <>{children}</>;
}
