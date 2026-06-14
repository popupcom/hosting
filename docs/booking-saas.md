# Booking SaaS (White-Label) — Architektur & Roadmap

> Multi-Mandanten-Buchungsstrecke für Hotels, betrieben als White-Label-SaaS mit
> monatlicher Verrechnung. Diese Datei dokumentiert den aktuellen Stand und die
> nächsten Schritte. Erstes Briefing siehe `PROJEKTBRIEFING.md` des Auftraggebers
> (Grünwald Resort Sölden), das als erster Referenz-Mandant dient.

## Idee in einem Satz

Hotels bekommen eine schnelle, voll trackbare Buchungsstrecke, die in der Agentur-
Plattform (popup.at) in Minuten eingerichtet wird, im Marken-Look des Hotels läuft
und sich über ein monatliches Abo finanziert.

## Warum in dieser Codebase

Die bestehende Laravel-/Filament-Anwendung verwaltet bereits Kund:innen, Projekte
und wiederkehrende Verrechnung. Ein Hotel-Mandant (`hotels`) hängt an einer
bestehenden `clients`-Zeile, sodass das Monatsabo über die vorhandenen Projekt-/
Billing-Werkzeuge abgerechnet werden kann. Kein zweites System nötig.

## Datenmodell

| Tabelle          | Zweck |
|------------------|-------|
| `hotels`         | Mandant. Branding (JSON), Tracking-IDs (JSON), Rechtstext-URLs (JSON), verschlüsselte PMS-/Payment-Credentials, Preis-/Storno-Defaults, Abo-Tarif. Verknüpft mit `clients`. |
| `booking_rates`  | Raten pro Hotel (z.B. Spar −10 %, Standard flexibel) inkl. Rabatt-, Anzahlungs-% und Bedingungstexten. |
| `booking_units`  | Unterkunfts-/Zimmertypen pro Hotel (Preis/Nacht, Kapazität, Bild, Casablanca `roomTypeId`). |
| `booking_extras` | Zusatzleistungen pro Hotel mit Abrechnungslogik (`per_person_per_day`, `per_night`, `once`, …). |
| `bookings`       | Konkrete Buchungen inkl. server-berechneter Beträge, Gastdaten, Zahlungs- und PMS-Referenzen, Attribution. |
| `booking_events` | First-Party-Funnel-Events je Session — Kern des „trackbaren“ Versprechens. |

Enums: `HotelStatus`, `SubscriptionPlan`, `BookingStatus`, `ExtraPricingUnit`,
`BookingEventType`.

## White-Label

`Hotel::brand()` mischt die Marken-Tokens des Mandanten über die Plattform-Defaults
(`Hotel::BRAND_DEFAULTS`). Die Buchungsstrecke (`resources/views/booking/flow.blade.php`)
rendert diese Tokens als CSS-Variablen und lädt die konfigurierte Google-Font. Leere
Felder fallen automatisch auf die Defaults zurück, sodass eine Strecke auch ohne
vollständige Marken-Konfiguration sauber aussieht.

## Tracking-Hoheit (USP)

Zwei Ebenen, bewusst redundant:

1. **Client-seitig** — `TrackingScriptBuilder` injiziert pro Mandant GA4 / Google Ads /
   Meta Pixel / GTM. Conversions landen direkt im Werbekonto des Hotels.
2. **Server-seitig (first-party)** — jeder Schritt schreibt ein `booking_events`-Record
   (`page_view` → `select_unit` → `add_payment_info` → `purchase`). Diese Daten gehören
   der Plattform, überleben Ad-Blocker/Cookie-Verlust und können später per
   Server-to-Server (Google Ads Offline Conversions / Meta CAPI) nachgemeldet werden.

Der `BookingFunnelWidget` im Admin zeigt daraus die Conversion-Rate der letzten 30 Tage.

## Preislogik (Source of Truth)

`App\Booking\PriceCalculator` rechnet **serverseitig** und ist verbindlich. Der Browser
zeigt nur eine Live-Schätzung; der berechnete Betrag wird vor dem Anlegen der Buchung
immer aus DB-Preisen neu ermittelt — ein manipuliertes Client-Payload kann den Preis
nicht verändern. Regeln (aus dem Prototyp übernommen):

- Kinder 0–2 frei, 3–6 −50 %, 7–14 −30 %, ab 15 Erwachsenenpreis
- Raten-Rabatt + Anzahlungs-%
- Extras je nach `pricing_unit`
- optionale Stornoversicherung als % des Reisepreises

Abgedeckt durch `tests/Unit/PriceCalculatorTest.php`.

## Öffentliche Routen

```
GET  /book/{hotel}                                 Buchungsstrecke (gerendert, themed)
POST /book/{hotel}/events                           Funnel-Event speichern
POST /book/{hotel}/quote                            Server-Preis für aktuelle Auswahl
POST /book/{hotel}/bookings                         Buchung (pending) anlegen
POST /book/{hotel}/bookings/{reference}/complete    Zahlung bestätigen (Demo-Stub)
GET  /book/{hotel}/confirmation/{reference}         Bestätigungsseite (+ Purchase-Tracking)
```

Nur Mandanten mit Status `active` sind öffentlich erreichbar.

## Admin (Filament)

Navigationsgruppe **Buchungs-SaaS**:

- `HotelResource` — Stammdaten, Branding (Color-Picker), Tracking-IDs, Preis-/Storno-
  Logik, Rechtstext-URLs, verschlüsselte PMS-/Payment-Credentials, Abo. Beim Anlegen
  werden automatisch zwei Standard-Raten erzeugt → Mandant ist sofort buchbar.
- Relation-Manager für **Unterkünfte**, **Raten**, **Zusatzleistungen** (Drag-&-Drop-
  Sortierung) — die „schnell einrichten“-UX.
- `BookingResource` — read-only Buchungsliste mit Funnel-Widget.

Demo-Mandant einspielen: `php artisan db:seed --class=BookingDemoSeeder`
(Grünwald Resort Sölden, 14 Unterkünfte, 6 Extras, 2 Raten).

## Bewusst noch offen (Stubs für die nächste Iteration)

Diese Punkte brauchen externe Zugänge/Abstimmung und sind klar isoliert:

1. **Casablanca IBE** — `inventory-cache` (Live-Verfügbarkeit/Preise), `shopping-carts`,
   `reservations`. Aktuell kommen Units/Preise aus der DB. Anbindung über einen
   `CasablancaClient`-Service (Vorlage: `casablanca-ibe-client.js`), der die
   verschlüsselten `pms_config`-Credentials des Mandanten nutzt. Fehlende Endpunkte bei
   `technik@casablanca.at` anfragen (siehe Briefing §99).
2. **hobex Online Payment** — `BookingFlowController::store()` legt eine `pending_payment`-
   Buchung an und gibt eine `complete_url` zurück. Diese simuliert aktuell die Zahlung.
   Produktiv: hobex Hosted Payment Page initialisieren (HMAC-signiert, MAC-Key nur
   serverseitig), `confirmUrl`-Webhook verifizieren, dann Casablanca-Reservierung
   anlegen und Bestätigung mailen.
3. **E-Mails** — Buchungs-/Anzahlungsbestätigung (de/en/it) an Gast + Hausbenachrichtigung.
4. **Abo-Billing** — `subscription_plan`/`SubscriptionPlan::monthlyPrice()` mit den
   bestehenden Projekt-/Billing-Strukturen verdrahten (z.B. Moco-Sync).
5. **Subdomain-/Custom-Domain-Routing** — `public_domain` je Mandant auf die `/book`-
   Routen mappen.
6. **DSGVO/Recht** — Cookie-Consent vor Tracking, Widerrufsbelehrung (§18 FAGG).

## Tests

- `tests/Unit/PriceCalculatorTest.php` — Preislogik
- `tests/Feature/Booking/BookingFlowTest.php` — Rendering, Event-Capture, Buchung mit
  serverseitigen Beträgen, Zahlungsabschluss, Validierung
- `tests/Feature/Booking/HotelAdminTest.php` — Filament-Seiten & Relation-Manager
