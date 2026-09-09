// Zentrale Env-Zugriffe. Nie beim Import werfen (Build ohne Secrets muss
// funktionieren) – Fehler erst beim tatsächlichen Zugriff.

export function env(name: string): string {
  const v = process.env[name];
  if (!v) throw new Error(`Umgebungsvariable ${name} fehlt`);
  return v;
}

export function envOpt(name: string): string | undefined {
  return process.env[name] || undefined;
}

export const appUrl = () =>
  envOpt("NEXT_PUBLIC_APP_URL") ??
  (envOpt("VERCEL_URL") ? `https://${process.env.VERCEL_URL}` : "http://localhost:3000");
