"use server";

import { revalidatePath } from "next/cache";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { verifyApprovalToken } from "@/lib/tokens";
import { logEvent } from "@/lib/events";
import { checkRateLimit } from "@/lib/ratelimit";
import { headers } from "next/headers";

async function loadOpenApproval(token: string) {
  const approvalId = verifyApprovalToken(token);
  if (!approvalId) return null;
  const h = await headers();
  const ip = h.get("x-forwarded-for")?.split(",")[0]?.trim() ?? "unknown";
  if (!(await checkRateLimit("freigabe", ip))) return null;
  const { data } = await supabaseAdmin().from("approvals")
    .select("id,submission_id,entscheidung,runde")
    .eq("id", approvalId).single();
  return data && data.entscheidung === "offen" ? data : null;
}

export async function kundeFreigebenAction(token: string) {
  const a = await loadOpenApproval(token);
  if (!a) return;
  const db = supabaseAdmin();
  await db.from("approvals")
    .update({ entscheidung: "freigegeben", entschieden_am: new Date().toISOString() })
    .eq("id", a.id);
  await db.from("submissions").update({ status: "pdf_ok" }).eq("id", a.submission_id);
  await logEvent(a.submission_id, "freigabe", "Einsender:in", `PDF freigegeben (Runde ${a.runde}).`);
  revalidatePath(`/freigabe/${token}`);
}

export async function kundeAenderungAction(token: string, formData: FormData) {
  const text = String(formData.get("text") ?? "").trim();
  if (!text) return;
  const a = await loadOpenApproval(token);
  if (!a) return;
  const db = supabaseAdmin();
  await db.from("approvals")
    .update({ entscheidung: "aenderung", entschieden_am: new Date().toISOString(), aenderungswunsch: text })
    .eq("id", a.id);
  await db.from("submissions")
    .update({ status: "pdf_aenderung", korrekturschleifen: a.runde })
    .eq("id", a.submission_id);
  await logEvent(a.submission_id, "freigabe", "Einsender:in",
    `Änderungswunsch (Runde ${a.runde}): „${text}“`);
  revalidatePath(`/freigabe/${token}`);
}
