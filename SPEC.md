# Gemeindezeitung Schruns – Produktiv-Umsetzung (Spezifikation v1)

Stack: **Next.js (App Router, TypeScript, Tailwind) auf Vercel · Supabase
(Postgres, Auth, Storage) · Upstash Redis (Rate Limiting, Cache) · Sentry
(Fehlerprotokollierung) · Resend (Transaktions-E-Mails)**

Design & Funktionsreferenz: `referenz-dummy/index.html` (Klick-Dummy im
Schruns-Design, Original-Wappen in `referenz-dummy/wappen.svg`). Der Dummy
definiert Layout, Wording und alle Workflows verbindlich.

## Rollen

| Rolle | Auth | Beschreibung |
|---|---|---|
| Einsender:in („Kunde“) | ohne Login (Phase 1) | Reicht Beiträge über das öffentliche Step-by-Step-Formular ein; erhält Freigabe-Link per E-Mail (signierter Token, kein Login nötig) |
| Redakteur:in | Supabase Auth (E-Mail-Einladung) | Sieht alle offenen Eingänge, weist sich Aufträge selbst zu, bearbeitet, startet Druckfreigabe |
| Super-Admin | Supabase Auth | Alles wie Redakteur:in + legt Redakteure an/deaktiviert sie, verwaltet Ausgaben/Kontingente/Preise |

Auth ausschließlich über Supabase Auth (Invite-Flow, Magic Link/Passwort –
nichts Eigenes bauen). Rollen als `app_role` in `profiles`-Tabelle, abgesichert
über RLS-Policies. Einsender-Konten (Login für Mitglieder) kommen in Phase 2.

## Öffentliche Seite (ohne Login)

Step-by-Step-Onboarding wie im Dummy:
1. Absender:in & Kontakt (Kategorie, Organisation, Name, Funktion, E-Mail, Telefon)
2. Beitrag (Ausgabe mit Redaktionsschluss, Rubrik, Titel, Einleitung, Text mit
   Zeichenzähler + Live-Umfangsschätzung, Credits) + Foto-Upload in
   Originalgröße (Supabase Storage, Auflösungs-Check „druckfähig“) +
   Rechte-/DSGVO-Checkbox
3. Bestätigung mit Referenznummer + E-Mail-Eingangsbestätigung

Zusätzlich (aus dem Dummy zu übernehmen): Inserate-/Partnerstory-Buchung mit
Live-Kontingenten und fixer Platzierung, Terminmeldung für den
Veranstaltungskalender. Rate Limiting (Upstash) auf allen öffentlichen
POST-Endpunkten und Upload-Routen.

## Redaktionsbereich (Login)

Alle Funktionen des Dummys, plus Zuweisung:
- Eingangsliste je Ausgabe (Status-/Art-/Rubrik-Filter, Suche), Kennzahlen,
  Kontingent-/Umsatzleiste
- **Selbstzuweisung:** Offene Eingänge kann sich ein:e Redakteur:in zuweisen
  („Übernehmen“); zugewiesene Aufträge zeigen Bearbeiter:in; Super-Admin kann
  umverteilen
- Detail: Prüf-Workflow (Neu → In Prüfung → Rückfrage → Für Agentur
  freigegeben), Rückfragen-Mails, interne Notizen, Verlauf (Audit-Trail),
  KI-Korrektur (Claude API, nur Rechtschreibung/Grammatik, Diff + Bestätigung),
  Foto-Downloads (einzeln + ZIP je Beitrag/gesamt)
- Druckfreigabe: PDF hochladen, Frist wählen (3/5/7/14 Tage), Versand des
  signierten Freigabe-Links an den Kunden; Erinnerung; Auto-Freigabe nach
  Fristablauf (Vercel Cron); manuelle Freigabe
- Heftspiegel (Seiten je Rubrik, 4er-Bogen), Terminkalender-Freigabe,
  Layout-Export (Word/ICML), Erinnerungs-Mail „Redaktionsschluss“
- Super-Admin: Redakteursverwaltung (einladen, deaktivieren), Ausgaben anlegen
  (Redaktionsschluss, Erscheinungstermin), Preise/Kontingente pflegen

## Kunden-Freigabeseite (signierter Link, ohne Login)

Detaillierte Druckfreigabe wie im Dummy: PDF-Ansicht, Frist mit Countdown und
Auto-Freigabe-Hinweis, „Freigeben“ oder Änderungswunsch (Freitext →
Korrekturschleife). **Zusammenfassung zeigt:** geplantes Online-/
Erscheinungsdatum der Ausgabe, wer den Auftrag wann bearbeitet hat, Anzahl der
bisherigen Korrekturschleifen.

## Datenmodell (Entwurf)

- `issues` (Ausgabe: label, redaktionsschluss, druckfreigabe_am, erscheint_am)
- `profiles` (user_id → Supabase Auth, name, role: superadmin|redakteur, aktiv)
- `submissions` (ref, art: beitrag|partnerstory|inserat1|inserat12, issue_id,
  rubrik, kategorie, organisation, kontakt (name/email/tel/funktion), titel,
  einleitung, text, credits, preis, platzierung, status, assigned_to →
  profiles, korrekturschleifen int, timestamps)
- `submission_photos` (submission_id, storage_path, original_name, w, h, bytes)
- `submission_events` (Verlauf/Audit: typ, akteur, text, created_at)
- `approvals` (submission_id, pdf_storage_path, frist, token_hash, gesendet_an,
  entschieden_am, entscheidung: freigegeben|aenderung|auto, runde int)
- `events_calendar` (Termine: titel, datum, zeit, ort, veranstalter,
  beschreibung, status, issue_id)
- `bookings`-Felder in `submissions` (preis, platzierung, druckdaten_path) –
  Kontingente werden transaktional gegen `issue_id`+`art` geprüft

## Infrastruktur

- Vercel: Hosting, Cron (Fristen/Auto-Freigabe, Erinnerungen), Env-Verwaltung
- Supabase: Postgres mit RLS, Auth (Invites für Redakteure), Storage-Buckets
  `photos` (Originale, privat) und `approvals` (PDFs, privat, signierte URLs)
- Upstash: `@upstash/ratelimit` auf öffentlichen Endpunkten; Cache für
  Kontingent-/Statistik-Abfragen
- Sentry: Client + Server + Edge, Source Maps im Build
- Secrets: nie im Repo; `.env.example` mit Platzhaltern, echte Werte in
  Vercel-Env + Claude-Umgebung (siehe `../gemeindezeitung-env/README.md`)

## Phase 2 (bewusst später)

Einsender-Login (Supabase Auth) mit „Meine Beiträge“/Entwürfen wie im Dummy,
Online-Ausgabe/E-Paper, Rechnungs-PDF für Buchungen.
