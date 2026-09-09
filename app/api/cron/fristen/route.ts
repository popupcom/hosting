import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { logEvent } from "@/lib/events";
import { env } from "@/lib/env";

// Täglicher Cron (vercel.json): abgelaufene Druckfreigabe-Fristen
// automatisch freigeben.
export async function GET(req: Request) {
  const auth = req.headers.get("authorization");
  if (auth !== `Bearer ${env("CRON_SECRET")}`) {
    return NextResponse.json({ error: "unauthorized" }, { status: 401 });
  }

  const db = supabaseAdmin();
  const { data: faellig } = await db.from("approvals")
    .select("id,submission_id,runde")
    .eq("entscheidung", "offen")
    .lt("frist", new Date().toISOString());

  for (const a of faellig ?? []) {
    await db.from("approvals")
      .update({ entscheidung: "auto", entschieden_am: new Date().toISOString() })
      .eq("id", a.id);
    await db.from("submissions").update({ status: "pdf_ok" }).eq("id", a.submission_id);
    await logEvent(a.submission_id, "freigabe", "System",
      `Frist abgelaufen – PDF gilt automatisch als freigegeben (Runde ${a.runde}).`);
  }

  return NextResponse.json({ ok: true, autoFreigaben: faellig?.length ?? 0 });
}
