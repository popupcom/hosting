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

Das Design ist der Startseite von www.schruns.at nachempfunden (Abgleich per
Screenshot): blaue Topbar mit weißem Schriftzug „Marktgemeinde“ und
Social-/Such-Icons, weiße Namensleiste mit großem „Schruns“-Schriftzug und
fetter schwarzer Hauptnavigation (Bürgerservice, Verwaltung, Politik, Unsere
Gemeinde), links das überlappende Gemeindewappen, darunter der Bildslider mit
Steuerung und Barrierefreiheits-Button, zentrierte Versal-Überschriften mit
Trennlinie sowie weiße Karten mit grauem Rahmen und stahlblauen Titeln.
Schrift: Open Sans. **Alle Farben liegen als CSS-Variablen im `:root`-Block
am Dateianfang** und können bei Bedarf exakt nachjustiert werden.

Zwei Grafiken sind bewusst austauschbar gehalten:

- **Wappen:** Im Dummy ist das Gemeindewappen als SVG nachgezeichnet
  (kein eigenes Logo, sondern eine Annäherung an das Original). Wird eine
  Datei **`wappen.png`** (das echte Wappen) neben die `index.html` gelegt,
  verwendet die Seite automatisch das Original.
- **Sliderbild:** Analog ersetzt eine Datei **`hero.jpg`** (z. B. das
  Blumenwiesen-Foto der Startseite) automatisch den SVG-Platzhalter.

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
