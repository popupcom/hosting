import "server-only";
import { envOpt } from "@/lib/env";

// Abstrakter Mailversand. Provider-Entscheidung ist bewusst offen:
// - RESEND_API_KEY gesetzt  -> Versand über Resend
// - sonst                   -> Mail wird geloggt (Entwicklung/Übergang)
// Ein späterer SMTP-Provider ergänzt hier nur einen weiteren Zweig.

export type Mail = { to: string; subject: string; text: string };

export async function sendMail(mail: Mail): Promise<{ sent: boolean; via: string }> {
  const resendKey = envOpt("RESEND_API_KEY");
  const from = envOpt("MAIL_FROM") ?? "Gemeindezeitung Schruns <onboarding@resend.dev>";
  if (resendKey) {
    const res = await fetch("https://api.resend.com/emails", {
      method: "POST",
      headers: { Authorization: `Bearer ${resendKey}`, "Content-Type": "application/json" },
      body: JSON.stringify({
        from,
        to: [mail.to],
        reply_to: envOpt("MAIL_REPLY_TO"),
        subject: mail.subject,
        text: mail.text,
      }),
    });
    if (!res.ok) throw new Error(`Resend-Fehler ${res.status}: ${await res.text()}`);
    return { sent: true, via: "resend" };
  }
  console.log(`[mail:log-only] an=${mail.to} betreff=${mail.subject}\n${mail.text}`);
  return { sent: false, via: "log" };
}
