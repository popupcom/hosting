# Gemeindezeitung Schruns – Klick-Dummy

Klickbarer Design-Dummy für ein Online-Tool, mit dem Vereine und Institutionen
Beiträge (Texte + Fotos) für die Gemeindezeitung der Marktgemeinde Schruns
(Ausgabe November 2026) einreichen können.

## Öffnen

`index.html` einfach im Browser öffnen – keine Installation, kein Server nötig.
Der Dummy ist eine einzelne, in sich geschlossene HTML-Datei.

Über die dunkle Leiste ganz oben kann zwischen den beiden Ansichten
umgeschaltet werden:

1. **Vereinsportal** (öffentliche Seite)
   - Schritt 1: Verein & Ansprechperson (Verein, Name, Funktion, E-Mail, Telefon)
   - Schritt 2: Beitrag (Titel, Einleitung, Text mit Zeichenzähler,
     Texter-/Fotografen-Hinweis) + Foto-Upload per Drag & Drop
     - Hinweisbox: Fotos unbearbeitet in Originalgröße hochladen (Druckqualität)
     - Automatische Prüfung der Bildauflösung („druckfähig“ / „evtl. zu klein“)
     - Rechte-/DSGVO-Bestätigung als Pflicht-Checkbox
   - Schritt 3: Bestätigung mit Referenznummer

2. **Redaktion (Backend)**
   - Dashboard-Kennzahlen, Termin-Leiste (Einsendeschluss, Druckfreigabe)
   - Liste aller Einreichungen mit Suche und Status-Filter
   - Detailansicht: Beitragstext, Kontaktdaten, Fotos mit Auflösung/Dateigröße
   - Redaktioneller Workflow: Neu → In Prüfung → Rückfrage → **Für Agentur
     freigegeben** (Gemeinde prüft die Texte und gibt sie an die Agentur frei)
   - Rückfragen an den Verein (simulierte E-Mail) + interne Notizen + Verlauf
   - Fotos gesammelt als ZIP herunterladen (im Dummy simuliert)
   - **PDF-Freigabeprozess:** Die Agentur lädt das fertig gestaltete PDF hoch
     und startet die Freigabe mit einstellbarer Frist (3/5/7/14 Tage). Der
     Verein erhält eine E-Mail mit Freigabe-Link; ohne Rückmeldung innerhalb
     der Frist gilt das PDF automatisch als freigegeben. Erinnerung und
     manuelle Freigabe durch die Redaktion sind möglich.

3. **PDF-Freigabe (Vereins-Ansicht)** – die Seite hinter dem E-Mail-Link:
   PDF-Vorschau, Frist-Hinweis mit Countdown, „Seite freigeben“ oder
   Änderungswunsch mit Freitext. Statuswechsel (PDF-Freigabe läuft →
   PDF freigegeben / Änderungswunsch) sind sofort in der Redaktionsliste
   sichtbar; abgelaufene Fristen werden beim Laden automatisch freigegeben.

Im Portal abgeschickte Beiträge erscheinen sofort in der Redaktionsansicht
(Speicherung nur lokal im Browser via `localStorage`, keine echte Übertragung).
„Demo zurücksetzen“ in der oberen Leiste stellt die vier Beispiel-Einreichungen
wieder her.

## Design

Das Design ist der Website www.schruns.at nachempfunden (Abgleich per
Screenshot), als eigenständige Landing Page ohne Hauptnavigation und ohne
Bildslider: blaue Topbar mit weißem Schriftzug „Marktgemeinde“ und
Social-/Such-Icons, weiße Namensleiste mit großem „Schruns“-Schriftzug,
links das überlappende Gemeindewappen, zentrierte Versal-Überschriften mit
Trennlinie sowie weiße Karten mit grauem Rahmen und stahlblauen Titeln.
Schrift: Open Sans. **Alle Farben liegen als CSS-Variablen im `:root`-Block
am Dateianfang** und können bei Bedarf exakt nachjustiert werden.

**Wappen:** Das Original-Wappen der Marktgemeinde Schruns liegt als
`wappen.svg` im Ordner und ist unverändert (1:1) in die Seite eingebettet –
im großen Portal-Header ebenso wie im kleinen Redaktions-Header.

Termine (Einsendeschluss 2. Oktober 2026 usw.), Amtszeiten und Kontaktdaten
im Footer sind Platzhalter.

## Nächster Schritt (echte Umsetzung)

Der Dummy ist bewusst backend-frei. Eine produktive Umsetzung würde in dieser
Laravel-Codebasis naheliegend so aussehen:

- Modelle `Submission` + `SubmissionPhoto` (Originaldateien unverändert im
  Storage, z. B. S3/lokal), Referenznummern wie im Dummy
- Öffentliches Formular (2 Schritte) mit signierten Upload-URLs bzw.
  Chunked-Upload für große Originalfotos
- Redaktionsbereich hinter Login: Liste, Status-Workflow, Rückfrage-Mails
  (Mailable mit Antwort-Link), ZIP-Export der Originale pro Beitrag oder gesamt
- DSGVO: Einwilligungstext, Löschkonzept nach Erscheinen der Ausgabe
