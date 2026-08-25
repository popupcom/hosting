# Gemeindezeitung Schruns – Klick-Dummy

Klickbarer Design-Dummy für ein Online-Tool, mit dem Vereine, Betriebe, die
Wirtschaftsgemeinschaft, Institutionen, Gemeindemitarbeiter:innen und
Privatpersonen Beiträge (Texte + Fotos) für die Gemeindezeitung der
Marktgemeinde Schruns (Ausgabe November 2026) einreichen können. Im Formular
wird dafür eine Absender-Kategorie gewählt; das Organisationsfeld ist nur
für Nicht-Privatpersonen Pflicht.

## Öffnen

`index.html` einfach im Browser öffnen – keine Installation, kein Server nötig.
Der Dummy ist eine einzelne, in sich geschlossene HTML-Datei.

Über die dunkle Leiste ganz oben kann zwischen den beiden Ansichten
umgeschaltet werden:

1. **Einreichportal** (öffentliche Seite)
   - **Konto für wiederkehrende Einsender (Demo-Login):** Stammdaten werden
     vorausgefüllt, „Meine Beiträge“ zeigt eigene Einreichungen samt Status,
     Entwürfe können gespeichert, weiterbearbeitet und später abgesendet werden.
   - Schritt 1: Absender:in & Kontakt (Kategorie, Organisation, Name, Funktion, E-Mail, Telefon)
   - Schritt 2: Beitrag mit **Ausgabe-Auswahl** (inkl. Redaktionsschluss-Anzeige)
     und **Rubrik** (Amtliches, Vereine, Veranstaltungen, Wirtschaft …), Titel,
     Einleitung, Text mit Zeichenzähler und **Live-Umfangsschätzung**
     („entspricht ca. X Seiten“), Texter-/Fotografen-Hinweis + Foto-Upload per
     Drag & Drop
     - Hinweisbox: Fotos unbearbeitet in Originalgröße hochladen (Druckqualität)
     - Automatische Prüfung der Bildauflösung („druckfähig“ / „evtl. zu klein“)
     - Rechte-/DSGVO-Bestätigung als Pflicht-Checkbox
   - Schritt 3: Bestätigung mit Referenznummer
   - **Inserate & Partnerstories buchen:** Angebotskarten mit Live-Kontingent
     (5 Partnerstories à € 850, 4 Inserate 1/1 à € 950, 8 Inserate 1/2 à
     € 550, jeweils netto). Ausgebuchte Formate sind gesperrt. Buchungsformular mit
     verbindlicher Bestätigung; bei Inseraten Druckdaten-Upload (oder später
     nachreichen), bei Partnerstories optional fixe Platzierung im Heft
     (U2, Seite 5, Heftmitte, U3, U4) – vergebene Plätze sind gesperrt.
     Referenzen PR-/INS-2026-xxx.
   - **Veranstaltung melden:** strukturierte Terminerfassung (Datum, Uhrzeit,
     Ort, Veranstalter, Kurzbeschreibung); nach Freigabe durch die Redaktion
     erscheinen die Termine automatisch in der Terminvorschau.

2. **Redaktion (Backend)**
   - **Ausgabenverwaltung:** Umschalter zwischen Ausgaben (z. B. November 2026 /
     März 2027) mit eigenem Redaktionsschluss; Liste, Kennzahlen, Kontingente
     und Heftspiegel beziehen sich immer auf die gewählte Ausgabe. Button
     „Erinnerung an Einsender senden“ (simulierte Redaktionsschluss-Mail).
   - Dashboard-Kennzahlen, Termin-Leiste (Einsendeschluss, Druckfreigabe)
   - Liste aller Einreichungen mit Suche, Status- und Art-Filter
     (redaktionelle Beiträge / Partnerstories / Inserate); Buchungen mit
     Format-Tag, Preis und Platzierung. Inventar-Leiste mit gebuchten
     Kontingenten und Umsatzsumme.
   - Detailansicht: Beitragstext, Kontaktdaten, Fotos mit Auflösung/Dateigröße;
     bei Buchungen zusätzlich Format, Preis, Platzierung und Druckdaten-Status
     (inkl. „ausständig“-Erinnerung). Buchungen laufen durch denselben
     Status- und PDF-Freigabeprozess.
   - Redaktioneller Workflow: Neu → In Prüfung → Rückfrage → **Für Agentur
     freigegeben** (Gemeinde prüft die Texte und gibt sie an die Agentur frei)
   - Rückfragen an die Ansprechperson (simulierte E-Mail) + interne Notizen + Verlauf
   - Fotos gesammelt als ZIP herunterladen (im Dummy simuliert)
   - **Heftspiegel:** Beiträge nach Rubriken gruppiert mit geschätzten Seiten
     (Richtwert 3.500 Zeichen/Seite + Fotoanteil), Summen je Rubrik, Inserate/
     PR, Terminseite und fixe Seiten; Gesamtumfang mit Hinweis auf den
     nächsten druckbaren 4er-Bogen.
   - **Terminkalender:** gemeldete Veranstaltungen prüfen/freigeben/entfernen,
     automatische Vorschau der Terminseite.
   - **Layout-Export (funktioniert echt):** erzeugt eine Word-Datei mit allen
     Beiträgen der Ausgabe, nach Rubriken gegliedert und mit Formatvorlagen
     (Titel/Einleitung/Fließtext/Credit) ausgezeichnet – inkl. Partnerstories
     und Terminseite. In der echten Umsetzung analog als InDesign-ICML.
   - **KI-Korrektur** beim Beitragstext: prüft ausschließlich Rechtschreibung,
     Grammatik und Zeichensetzung (keine inhaltlichen oder stilistischen
     Änderungen), zeigt die Vorschläge als Vorher/Nachher-Markierung und
     übernimmt sie erst nach Bestätigung durch die Redaktion; jede Übernahme
     wird im Verlauf dokumentiert. Im Dummy simuliert (Beispiel: Beitrag des
     FC Schruns enthält absichtliche Fehler); in der echten Umsetzung
     übernimmt das die Claude-API mit eng gefasstem Systemprompt.
   - **PDF-Freigabeprozess:** Die Agentur lädt das fertig gestaltete PDF hoch
     und startet die Freigabe mit einstellbarer Frist (3/5/7/14 Tage). Die
     Ansprechperson erhält eine E-Mail mit Freigabe-Link; ohne Rückmeldung innerhalb
     der Frist gilt das PDF automatisch als freigegeben. Erinnerung und
     manuelle Freigabe durch die Redaktion sind möglich.

3. **PDF-Freigabe (Einsender-Ansicht)** – die Seite hinter dem E-Mail-Link:
   PDF-Vorschau, Frist-Hinweis mit Countdown, „Seite freigeben“ oder
   Änderungswunsch mit Freitext. Statuswechsel (PDF-Freigabe läuft →
   PDF freigegeben / Änderungswunsch) sind sofort in der Redaktionsliste
   sichtbar; abgelaufene Fristen werden beim Laden automatisch freigegeben.

Im Portal abgeschickte Beiträge erscheinen sofort in der Redaktionsansicht
(Speicherung nur lokal im Browser via `localStorage`, keine echte Übertragung).
„Demo zurücksetzen“ in der oberen Leiste stellt die fünf Beispiel-Einreichungen
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
