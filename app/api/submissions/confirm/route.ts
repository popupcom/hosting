import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { verifyApprovalToken } from "@/lib/tokens";

// Markiert die Fotos einer Einreichung nach erfolgreichem Direkt-Upload
// als hochgeladen. Autorisierung über das signierte Token aus dem
// Einreichungs-Response.
export async function POST(req: Request) {
  const body = await req.json().catch(() => null);
  const submissionId = body?.token ? verifyApprovalToken(body.token) : null;
  if (!submissionId) return NextResponse.json({ error: "Ungültiges Token." }, { status: 403 });

  const db = supabaseAdmin();
  const { data: rows } = await db.from("submission_photos")
    .update({ uploaded: true })
    .eq("submission_id", submissionId)
    .select("id");
  await db.from("submission_events").insert({
    submission_id: submissionId, typ: "system", akteur: "System",
    text: `${rows?.length ?? 0} Fotos in Originalgröße hochgeladen.`,
  });
  return NextResponse.json({ ok: true });
}
