# Excel-Import „Supportpakete Import“

## Ziel

Einmaliger und wiederholter Import der Datei **„WordPress Support Pakete – Status der Updates“** (Blatt **Supportpakete Import**) zur Pflege von Kund:innen, Projekten, Projekt-Leistungen (Supportpakete), VK, Moco-Sync-Status und Wartungs-/Notizdaten **ohne** bestehende Datensätze zu löschen.

## Spalten → Domänenmodell

| Excel-Spalte | Ziel |
|--------------|------|
| `customer_name` | `clients.name` (Treffer case-insensitive per `TRIM`; neu: `slug` aus Name, `status` = active) |
| `website` | `projects.url` (normalisiert: trim, optional `https://`, trailing `/` entfernt); `projects.name` aus Hostname |
| `support_package` | Abgleich `service_catalog_items` mit `category = support_package` (Name/Slug); sonst neue Katalogzeile |
| `price_month_2026` / `price_year_2026` | `project_services.custom_sales_price` je nach abgeleitetem Intervall |
| `price_change_from` | `project_services.price_change_effective_from` (Datum, sonst leer) |
| `billing_period` | Hilfstext in `project_services.notes` + Ableitung `custom_billing_interval` |
| `update_status` | Wenn nicht leer: `maintenance_histories` (Typ `support_package_excel_snapshot`), Feld `result` = Status, `notes` inkl. ToDo/Kommentar, Marker in `notes` für Idempotenz |
| `open_todos` / `comment` | In `maintenance_histories.notes` bzw. ergänzend in `project_services.notes` |
| `invoice_reference` | `project_services.moco_invoice_reference` |
| `moco_status` | `project_services.moco_sync_status` (Mapping siehe unten) |

Moco bleibt führend für Rechnungslegung; Referenz und Sync-Status dienen der **internen Übersicht**.

## Idempotenz

- **Kund:in:** Suche per `LOWER(TRIM(name))`. Neu nur wenn kein Treffer.
- **Projekt:** Suche per `client_id` + normalisierter `url`. `updateOrCreate` auf diesen Schlüsseln (kein Löschen).
- **Projekt-Leistung:** `updateOrCreate` auf `(project_id, service_catalog_item_id)`.
- **Wartung:** Erste Zeile der Notiz `[excel-import-row:{sha256}]` aus `(customer_name|website|support_package)`. Existiert Eintrag mit gleichem Projekt und Marker → **aktualisieren**, sonst anlegen. Leeres `update_status` → kein Wartungseintrag.

## Moco-Sync-Mapping (Excel → Enum)

| Excel (`moco_status`) | `project_services.moco_sync_status` |
|----------------------|-------------------------------------|
| offen (i. S.) | `ready` |
| erledigt, verrechnet | `synced` |
| fehler | `error` |
| leer | `not_synced` |

## Verrechnungsintervall

Aus `billing_period` (Freitext): Enthält „jahr“ / „yearly“ / „jährlich“ → `yearly`, sonst Standard **`monthly`**. VK: bei `yearly` bevorzugt `price_year_2026`, sonst `price_month_2026` (Fallback wenn ein Preis fehlt).

## Technik

- Einlesen mit **PhpSpreadsheet** (`PhpOffice\PhpSpreadsheet\IOFactory::load`), Blattname **Supportpakete Import**, Fallback erstes Blatt.
- **Filament:** Seite **Einstellungen → Import** (nur Admin), Upload, Vorschau (erste Zeilen), Validierung `customer_name` + `website`, Import-Button, Log-Zähler und Zeilenfehler.

## Nicht importiert

Keine Passwörter, keine sensiblen Zahlungsdaten; nur die genannten Spalten.
