# Gemeindezeitung Schruns

Online-Tool der Marktgemeinde Schruns: Beiträge und Fotos für die
Gemeindezeitung einreichen, redaktionell prüfen und per Druckfreigabe-Link
freigeben lassen. Design nach www.schruns.at (siehe `referenz-dummy/`).

**Stack:** Next.js (App Router, TypeScript, Tailwind) · Vercel · Supabase
(Postgres, Auth, Storage) · Upstash Redis (Rate Limiting) · Sentry ·
E-Mail-Provider austauschbar (`lib/mailer.ts`, aktuell Resend oder Log-Modus).

## Funktionsumfang (Phase 1)

- **Öffentliche Startseite ohne Login:** Step-by-Step-Einreichung
  (Absender:in → Beitrag mit Ausgabe/Rubrik/Umfangsschätzung → Foto-Upload in
  Originalgröße direkt in den Storage → Referenznummer + Eingangsbestätigung)
- **Redaktionsbereich** (Supabase Auth): Eingangsliste mit Kennzahlen und
  Filtern, **Selbstzuweisung** offener Aufträge, Statusworkflow, Rückfragen
  per E-Mail, interne Notizen, lückenloser Verlauf, Foto-Originale
- **Druckfreigabe:** PDF hochladen, Frist wählen (3/5/7/14 Tage), signierter
  Freigabe-Link an die Einsender:in; Erinnerung; automatische Freigabe nach
  Fristablauf (Vercel Cron); Korrekturschleifen werden gezählt
- **Kunden-Freigabeseite** (ohne Login): PDF, Zusammenfassung (Erscheinungs-
  datum, Bearbeiter:in, Korrekturschleifen), Freigeben oder Änderungswunsch
- **Super-Admin:** lädt Redakteur:innen per Supabase-Invite ein, verwaltet
  Rollen, kann Konten deaktivieren

Weitere Dummy-Funktionen (Inserate-Buchung, Terminkalender, Heftspiegel,
KI-Korrektur, Layout-Export) folgen als nächste Ausbaustufen – Referenz und
Verhalten sind in `referenz-dummy/index.html` und `SPEC.md` definiert.

## Setup

1. **Supabase-Projekt** anlegen → Werte in `.env.local` (Vorlage:
   `.env.example`; echte Werte liegen im separaten Secrets-Ordner, nie im Repo).
2. Migration einspielen: `npx supabase db push --db-url "$SUPABASE_DB_URL"`
   (oder Inhalt von `supabase/migrations/0001_init.sql` im SQL-Editor ausführen).
3. Seeding (erste Ausgabe + Super-Admin-Einladung): `npm run seed`
4. Lokal: `npm run dev` · Deployment: Repo bei Vercel importieren, Env-Variablen
   setzen (`vercel env`), `vercel deploy`.
5. Supabase Auth → URL Configuration: Site-URL + `/auth/callback` als Redirect
   erlauben.

## Sicherheit

- Alle Schreibzugriffe laufen über Server-Code (Service Role); RLS ist aktiv
  und standardmäßig geschlossen. Rollenprüfung: `lib/auth.ts`.
- Öffentliche Endpunkte sind über Upstash rate-limitiert (`lib/ratelimit.ts`).
- Freigabe-Links sind HMAC-signiert (`lib/tokens.ts`), Storage-Buckets privat
  (Zugriff nur über kurzlebige signierte URLs).
- Secrets: nie im Repo. `.env*` ist gitignored; Werte liegen bei Vercel und
  in der Claude-Umgebung.
