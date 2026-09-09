import { NextResponse } from "next/server";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { submissionSchema } from "@/lib/validation";
import { checkRateLimit, clientIp } from "@/lib/ratelimit";
import { signApprovalToken } from "@/lib/tokens";
import { neueRef } from "@/lib/format";
import { sendMail } from "@/lib/mailer";

export async function POST(req: Request) {
  if (!(await checkRateLimit("submit", clientIp(req)))) {
    return NextResponse.json({ error: "Zu viele Anfragen – bitte später erneut versuchen." }, { status: 429 });
  }

  const parsed = submissionSchema.safeParse(await req.json().catch(() => null));
  if (!parsed.success) {
    return NextResponse.json(
      { error: parsed.error.issues[0]?.message ?? "Ungültige Eingabe." },
      { status: 400 },
    );
  }
  const d = parsed.data;
  const db = supabaseAdmin();

  const { data: issue } = await db
    .from("issues").select("id,slug,redaktionsschluss")
    .eq("slug", d.issueSlug).eq("aktiv", true).single();
  if (!issue) return NextResponse.json({ error: "Ausgabe nicht gefunden." }, { status: 400 });
  if (new Date(issue.redaktionsschluss) < new Date()) {
    return NextResponse.json({ error: "Der Redaktionsschluss dieser Ausgabe ist bereits vorbei." }, { status: 400 });
  }

  // Referenznummer je Jahr (bei Kollision einmal neu versuchen)
  const jahr = parseInt(d.issueSlug.slice(0, 4), 10) || new Date().getFullYear();
  let submission: { id: string; ref: string } | null = null;
  for (let versuch = 0; versuch < 2 && !submission; versuch++) {
    const { count } = await db.from("submissions")
      .select("id", { count: "exact", head: true })
      .like("ref", `GZ-${jahr}-%`);
    const ref = neueRef("beitrag", jahr, (count ?? 0) + 1 + versuch);
    const { data, error } = await db.from("submissions").insert({
      ref, art: "beitrag", issue_id: issue.id, rubrik: d.rubrik,
      kategorie: d.kategorie, organisation: d.organisation || null,
      vorname: d.vorname, nachname: d.nachname, funktion: d.funktion || null,
      email: d.email, telefon: d.telefon || null,
      titel: d.titel, einleitung: d.einleitung || null, text: d.text,
      credits: d.credits || null,
    }).select("id,ref").single();
    if (!error) submission = data;
  }
  if (!submission) return NextResponse.json({ error: "Einreichung fehlgeschlagen." }, { status: 500 });

  // Signierte Upload-URLs für Fotos in Originalgröße
  const uploads: { path: string; token: string }[] = [];
  for (let i = 0; i < d.photos.length; i++) {
    const p = d.photos[i];
    const safe = p.name.replace(/[^\w.\-]+/g, "_").slice(-80);
    const path = `${submission.id}/${i + 1}_${safe}`;
    await db.from("submission_photos").insert({
      submission_id: submission.id, storage_path: path, original_name: p.name,
      width: p.width, height: p.height, bytes: p.bytes,
    });
    const { data: signed, error: signErr } = await db.storage
      .from("photos").createSignedUploadUrl(path);
    if (signErr || !signed) {
      return NextResponse.json({ error: "Upload-Vorbereitung fehlgeschlagen." }, { status: 500 });
    }
    uploads.push({ path, token: signed.token });
  }

  await db.from("submission_events").insert({
    submission_id: submission.id, typ: "system", akteur: "System",
    text: `Beitrag eingereicht (${d.photos.length} Fotos angekündigt).`,
  });

  await sendMail({
    to: d.email,
    subject: `Eingangsbestätigung ${submission.ref} – Gemeindezeitung Schruns`,
    text: `Grüß Gott ${d.vorname} ${d.nachname},\n\nvielen Dank! Ihr Beitrag „${d.titel}“ wurde unter der Referenz ${submission.ref} an die Redaktion der Gemeindezeitung übermittelt.\n\nBei Rückfragen melden wir uns unter dieser E-Mail-Adresse.\n\nMarktgemeinde Schruns`,
  }).catch((e) => console.error("[mail]", e));

  return NextResponse.json({
    ok: true,
    ref: submission.ref,
    confirmToken: signApprovalToken(submission.id),
    uploads,
  });
}
