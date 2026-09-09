"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { requireEditor, requireSuperadmin } from "@/lib/auth";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { supabaseServer } from "@/lib/supabase/server";
import { logEvent } from "@/lib/events";
import { sendMail } from "@/lib/mailer";
import { signApprovalToken } from "@/lib/tokens";
import { appUrl } from "@/lib/env";
import { fmtDate, STATUS_LABEL } from "@/lib/format";

export async function signOutAction() {
  const supabase = await supabaseServer();
  await supabase.auth.signOut();
  redirect("/admin/login");
}

export async function assignToMeAction(submissionId: string) {
  const me = await requireEditor();
  const db = supabaseAdmin();
  const { data } = await db.from("submissions")
    .update({ assigned_to: me.user_id, assigned_at: new Date().toISOString() })
    .eq("id", submissionId).is("assigned_to", null)
    .select("id").single();
  if (data) {
    await logEvent(submissionId, "zuweisung", me.name || me.email, "Auftrag übernommen.", me.user_id);
  }
  revalidatePath("/admin");
  revalidatePath(`/admin/beitrag/${submissionId}`);
}

export async function setStatusAction(submissionId: string, status: string) {
  const me = await requireEditor();
  if (!["neu", "pruefung", "rueckfrage", "freigegeben"].includes(status)) return;
  await supabaseAdmin().from("submissions").update({ status }).eq("id", submissionId);
  await logEvent(submissionId, "status", me.name || me.email,
    `Status geändert auf „${STATUS_LABEL[status]}“.`, me.user_id);
  revalidatePath(`/admin/beitrag/${submissionId}`);
  revalidatePath("/admin");
}

export async function rueckfrageAction(submissionId: string, formData: FormData) {
  const me = await requireEditor();
  const text = String(formData.get("text") ?? "").trim();
  if (!text) return;
  const db = supabaseAdmin();
  const { data: s } = await db.from("submissions")
    .select("email,vorname,nachname,titel,ref").eq("id", submissionId).single();
  if (!s) return;
  await db.from("submissions").update({ status: "rueckfrage" }).eq("id", submissionId);
  await sendMail({
    to: s.email,
    subject: `Rückfrage zu Ihrem Beitrag ${s.ref} – Gemeindezeitung Schruns`,
    text: `Grüß Gott ${s.vorname} ${s.nachname},\n\nzu Ihrem Beitrag „${s.titel}“ haben wir eine Rückfrage:\n\n${text}\n\nBitte antworten Sie einfach auf diese E-Mail.\n\nRedaktion Gemeindezeitung Schruns`,
  }).catch((e) => console.error("[mail]", e));
  await logEvent(submissionId, "rueckfrage", me.name || me.email,
    `Rückfrage gesendet: „${text}“`, me.user_id);
  revalidatePath(`/admin/beitrag/${submissionId}`);
}

export async function notizAction(submissionId: string, formData: FormData) {
  const me = await requireEditor();
  const text = String(formData.get("text") ?? "").trim();
  if (!text) return;
  await logEvent(submissionId, "notiz", me.name || me.email, `Interne Notiz: ${text}`, me.user_id);
  revalidatePath(`/admin/beitrag/${submissionId}`);
}

export async function sendApprovalAction(submissionId: string, formData: FormData) {
  const me = await requireEditor();
  const pdf = formData.get("pdf") as File | null;
  const tage = parseInt(String(formData.get("tage") ?? "7"), 10) || 7;
  if (!pdf || pdf.size === 0) return;
  const db = supabaseAdmin();
  const { data: s } = await db.from("submissions")
    .select("id,ref,email,vorname,nachname,titel,korrekturschleifen").eq("id", submissionId).single();
  if (!s) return;

  const runde = (s.korrekturschleifen ?? 0) + 1;
  const path = `${submissionId}/runde-${runde}-${Date.now()}.pdf`;
  const { error: upErr } = await db.storage.from("approvals")
    .upload(path, Buffer.from(await pdf.arrayBuffer()), { contentType: "application/pdf" });
  if (upErr) throw new Error(`PDF-Upload fehlgeschlagen: ${upErr.message}`);

  const frist = new Date(Date.now() + tage * 86400000);
  const { data: approval } = await db.from("approvals").insert({
    submission_id: submissionId, runde, pdf_storage_path: path, pdf_name: pdf.name,
    gesendet_an: s.email, frist: frist.toISOString(),
  }).select("id").single();
  if (!approval) return;

  await db.from("submissions").update({ status: "pdf_offen" }).eq("id", submissionId);
  const link = `${appUrl()}/freigabe/${signApprovalToken(approval.id)}`;
  await sendMail({
    to: s.email,
    subject: `Druckfreigabe für Ihren Beitrag ${s.ref} – Gemeindezeitung Schruns`,
    text: `Grüß Gott ${s.vorname} ${s.nachname},\n\nIhr Beitrag „${s.titel}“ wurde für die Gemeindezeitung gestaltet. Bitte prüfen und geben Sie die Seite frei:\n\n${link}\n\nRückmeldefrist: ${fmtDate(frist)}. Ohne Rückmeldung gilt die Seite danach automatisch als freigegeben.\n\nRedaktion Gemeindezeitung Schruns`,
  }).catch((e) => console.error("[mail]", e));
  await logEvent(submissionId, "freigabe", me.name || me.email,
    `Druckfreigabe (Runde ${runde}) an ${s.email} gesendet. Frist: ${fmtDate(frist)} (${tage} Tage, danach automatische Freigabe).`, me.user_id);
  revalidatePath(`/admin/beitrag/${submissionId}`);
  revalidatePath("/admin");
}

export async function remindApprovalAction(submissionId: string) {
  const me = await requireEditor();
  const db = supabaseAdmin();
  const { data: a } = await db.from("approvals")
    .select("id,gesendet_an,frist,submission:submissions(ref,titel,vorname,nachname)")
    .eq("submission_id", submissionId).eq("entscheidung", "offen")
    .order("runde", { ascending: false }).limit(1).single();
  if (!a) return;
  const sub = Array.isArray(a.submission) ? a.submission[0] : a.submission;
  const link = `${appUrl()}/freigabe/${signApprovalToken(a.id)}`;
  await sendMail({
    to: a.gesendet_an,
    subject: `Erinnerung: Druckfreigabe ${sub?.ref} – Frist ${fmtDate(a.frist)}`,
    text: `Grüß Gott,\n\nfreundliche Erinnerung an die offene Druckfreigabe für „${sub?.titel}“:\n\n${link}\n\nOhne Rückmeldung bis ${fmtDate(a.frist)} gilt die Seite automatisch als freigegeben.\n\nRedaktion Gemeindezeitung Schruns`,
  }).catch((e) => console.error("[mail]", e));
  await logEvent(submissionId, "mail", me.name || me.email, "Erinnerung zur Druckfreigabe gesendet.", me.user_id);
  revalidatePath(`/admin/beitrag/${submissionId}`);
}

export async function manualApproveAction(submissionId: string) {
  const me = await requireEditor();
  const db = supabaseAdmin();
  await db.from("approvals")
    .update({ entscheidung: "freigegeben", entschieden_am: new Date().toISOString() })
    .eq("submission_id", submissionId).eq("entscheidung", "offen");
  await db.from("submissions").update({ status: "pdf_ok" }).eq("id", submissionId);
  await logEvent(submissionId, "status", me.name || me.email, "PDF manuell als freigegeben markiert.", me.user_id);
  revalidatePath(`/admin/beitrag/${submissionId}`);
  revalidatePath("/admin");
}

// ---------- Super-Admin: Redakteursverwaltung ----------

export async function inviteEditorAction(formData: FormData) {
  const me = await requireSuperadmin();
  const email = String(formData.get("email") ?? "").trim().toLowerCase();
  const name = String(formData.get("name") ?? "").trim();
  const role = formData.get("role") === "superadmin" ? "superadmin" : "redakteur";
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) return;
  const db = supabaseAdmin();
  const { data, error } = await db.auth.admin.inviteUserByEmail(email, {
    data: { name },
    redirectTo: `${appUrl()}/auth/callback?next=/admin/passwort`,
  });
  if (error) {
    console.error("[invite]", error.message);
    return;
  }
  await db.from("profiles")
    .update({ name, role, aktiv: true })
    .eq("user_id", data.user.id);
  console.log(`[admin] ${me.email} hat ${email} als ${role} eingeladen`);
  revalidatePath("/admin/redakteure");
}

export async function toggleEditorAction(userId: string, aktiv: boolean) {
  const me = await requireSuperadmin();
  if (me.user_id === userId) return; // sich selbst nicht aussperren
  await supabaseAdmin().from("profiles").update({ aktiv }).eq("user_id", userId);
  revalidatePath("/admin/redakteure");
}
