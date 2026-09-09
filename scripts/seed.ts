/**
 * Einmaliges Seeding: erste Ausgabe + Super-Admin.
 * Aufruf:  npm run seed   (benötigt NEXT_PUBLIC_SUPABASE_URL,
 * SUPABASE_SERVICE_ROLE_KEY, SEED_SUPERADMIN_EMAIL, NEXT_PUBLIC_APP_URL)
 */
import { createClient } from "@supabase/supabase-js";

async function main() {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  const adminEmail = process.env.SEED_SUPERADMIN_EMAIL;
  if (!url || !key || !adminEmail) {
    throw new Error("NEXT_PUBLIC_SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY und SEED_SUPERADMIN_EMAIL setzen.");
  }
  const db = createClient(url, key, { auth: { persistSession: false } });

  // Ausgabe November 2026
  await db.from("issues").upsert({
    slug: "2026-11",
    label: "November 2026",
    redaktionsschluss: "2026-10-02T23:59:00+02:00",
    druckfreigabe_am: "2026-10-23",
    erscheint_am: "2026-11-06",
  }, { onConflict: "slug" });
  console.log("Ausgabe 2026-11 angelegt/aktualisiert.");

  // Super-Admin einladen (falls noch nicht vorhanden)
  const { data: existing } = await db.from("profiles").select("user_id").eq("email", adminEmail).maybeSingle();
  if (existing) {
    await db.from("profiles").update({ role: "superadmin", aktiv: true }).eq("user_id", existing.user_id);
    console.log(`${adminEmail} ist Super-Admin.`);
  } else {
    const redirectTo = `${process.env.NEXT_PUBLIC_APP_URL ?? "http://localhost:3000"}/auth/callback?next=/admin/passwort`;
    const { data, error } = await db.auth.admin.inviteUserByEmail(adminEmail, { redirectTo });
    if (error) throw error;
    await db.from("profiles").update({ role: "superadmin" }).eq("user_id", data.user.id);
    console.log(`Einladung an ${adminEmail} gesendet (Super-Admin).`);
  }
}

main().then(() => process.exit(0)).catch((e) => { console.error(e); process.exit(1); });
