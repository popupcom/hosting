import "server-only";
import { supabaseAdmin } from "@/lib/supabase/admin";

export async function logEvent(
  submissionId: string,
  typ: string,
  akteur: string,
  text: string,
  akteurId?: string,
) {
  await supabaseAdmin().from("submission_events").insert({
    submission_id: submissionId, typ, akteur, text, akteur_id: akteurId ?? null,
  });
}
