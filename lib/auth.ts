import "server-only";
import { redirect } from "next/navigation";
import { supabaseServer } from "@/lib/supabase/server";
import { supabaseAdmin } from "@/lib/supabase/admin";

export type Profile = {
  user_id: string;
  name: string;
  email: string;
  role: "superadmin" | "redakteur";
  aktiv: boolean;
};

// Liefert das Profil der eingeloggten Person oder leitet zum Login um.
export async function requireEditor(): Promise<Profile> {
  const supabase = await supabaseServer();
  const { data: { user } } = await supabase.auth.getUser();
  if (!user) redirect("/admin/login");
  const { data: profile } = await supabaseAdmin()
    .from("profiles").select("*").eq("user_id", user.id).single();
  if (!profile || !profile.aktiv) redirect("/admin/login?inaktiv=1");
  return profile as Profile;
}

export async function requireSuperadmin(): Promise<Profile> {
  const p = await requireEditor();
  if (p.role !== "superadmin") redirect("/admin");
  return p;
}
