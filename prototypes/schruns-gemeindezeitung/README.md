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
   - Status-Workflow: Neu → In Prüfung → Rückfrage → Freigegeben
   - Rückfragen an den Verein (simulierte E-Mail) + interne Notizen + Verlauf
   - Fotos gesammelt als ZIP herunterladen (im Dummy simuliert)

Im Portal abgeschickte Beiträge erscheinen sofort in der Redaktionsansicht
(Speicherung nur lokal im Browser via `localStorage`, keine echte Übertragung).
„Demo zurücksetzen“ in der oberen Leiste stellt die vier Beispiel-Einreichungen
wieder her.

## Design

Der direkte Zugriff auf www.schruns.at war aus der Entwicklungsumgebung nicht
möglich, daher ist das Design eine Annäherung auf Basis der Wappenfarben
(Gold/Schwarz, gekreuzte Montafoner Schlüssel) und typischer Gemeinde-Portale.
**Alle Farben liegen als CSS-Variablen im `:root`-Block am Dateianfang** –
für die Anpassung an die exakte CI von schruns.at genügt es, diese Werte zu
tauschen. Wappen-Grafik, Navigationspunkte, Termine (Einsendeschluss
2. Oktober 2026 usw.) und Kontaktdaten im Footer sind Platzhalter.

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
