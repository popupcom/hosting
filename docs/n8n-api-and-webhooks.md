# n8n: API- und Webhook-Integration

Dieses Dokument beschreibt die **REST-API**, **Authentifizierung**, **eingehende Webhooks**, **Sync-Status**, **externe IDs** (Moco, ManageWP, AutoDNS) und **empfohlene n8n-Workflows** für das Popup Hosting Overview Tool.

## Überblick

| Bereich | Technologie |
|--------|----------------|
| API-Prefix | `/api` (Laravel-Standard) |
| Version | `/api/v1/...` |
| Auth (API) | Laravel Sanctum Personal Access Token |
| Auth (Webhooks) | Bearer-Token **oder** HMAC-SHA256 (`X-Webhook-Signature`) |
| Sync-Status | Tabelle `integration_sync_states` (Morph zu Kund:in, Projekt, Domain, Kostenposition) + bestehende Felder (`moco_sync_status`, `moco_customer_id`, …) |
| Webhook-Audit | Tabelle `integration_webhook_events` (Payload, Dedupe-Key, `processed_at`) |

## Umgebungsvariablen (`.env`)

```env
# Sanctum (optional; Standard reicht für Server-zu-Server)
SANCTUM_STATEFUL_DOMAINS=

# Webhook: pro Integration entweder Bearer ODER HMAC (mindestens eines setzen)
WEBHOOK_BEARER_TOKEN_MOCO=
WEBHOOK_BEARER_TOKEN_MANAGEWP=
WEBHOOK_BEARER_TOKEN_AUTODNS=

WEBHOOK_HMAC_SECRET_MOCO=
WEBHOOK_HMAC_SECRET_MANAGEWP=
WEBHOOK_HMAC_SECRET_AUTODNS=
```

**HMAC-Signatur:** Roh-Body (JSON wie gesendet), Header `X-Webhook-Signature` mit Wert `sha256=<hex>` oder nur `<hex>` (gleich `hash_hmac('sha256', $rawBody, $secret)`).

**Dedupe:** Optional Header `X-Webhook-Dedupe` oder JSON-Felder `event_id` / `dedupe_key`. Bei gleichem Wert liefert die API `duplicate: true` und verarbeitet das Ereignis nicht erneut.

## API-Authentifizierung (n8n → Laravel)

1. Filament-User (oder beliebiger `users`-Datensatz) verwenden.
2. Token mit Fähigkeit **`integrations`** erstellen (Pflicht für alle v1-Integration-Endpunkte):

```bash
php artisan tinker
>>> $u = \App\Models\User::where('email', 'office@popup.at')->first();
>>> $u->createToken('n8n-prod', ['integrations'])->plainTextToken;
```

3. In n8n beim HTTP Request Node: **Authentication → Header Auth** oder Header  
   `Authorization: Bearer <plainTextToken>`

### Token-Fähigkeiten

- Aktuell ein einheitliches Ability: **`integrations`** (Lesen und Schreiben der Integrations-Endpunkte).
- Fehlt die Fähigkeit → HTTP **403**. Ungültiger/fehlender Token → **401**.

### Rate Limits

- `api`: 120 Requests/Minute (pro User-ID bzw. IP).
- `webhooks`: 60 Requests/Minute (pro IP).

Konfiguration: `App\Providers\AppServiceProvider` (`RateLimiter::for`).

## REST-Endpunkte (`/api/v1`)

Alle folgenden Routen (außer Webhooks) erfordern: `Authorization: Bearer …` + Ability `integrations`.

| Methode | Pfad | Zweck |
|--------|------|--------|
| GET | `/api/v1/me` | Health / aktueller User |
| GET | `/api/v1/clients` | Kund:innen (Query: `search`, `per_page`) |
| GET | `/api/v1/clients/{id}` | Detail inkl. `integration_sync_states` |
| PATCH | `/api/v1/clients/{id}` | u. a. `moco_customer_id` setzen |
| GET | `/api/v1/projects` | Projekte (`client_id`, `search`, `per_page`) |
| GET | `/api/v1/projects/{id}` | Detail |
| PATCH | `/api/v1/projects/{id}` | u. a. `moco_project_id`, `managewp_site_id` |
| GET | `/api/v1/project-domains` | Domains (`project_id`, `search`) |
| GET | `/api/v1/project-domains/{id}` | Detail |
| PATCH | `/api/v1/project-domains/{id}` | u. a. `autodns_id` |
| GET | `/api/v1/cost-line-items` | Kostenpositionen (`client_id`, `project_id`, `moco_sync_status`) |
| GET | `/api/v1/cost-line-items/{id}` | Detail |
| PATCH | `/api/v1/cost-line-items/{id}` | u. a. `moco_sync_status` |
| GET | `/api/v1/integration-sync-states` | Filter: `provider`, `status`, `syncable_type`, `external_id` |
| POST | `/api/v1/integration-sync-states` | Upsert Sync-Zeile (siehe JSON-Vertrag unten) |
| PATCH | `/api/v1/integration-sync-states/{id}` | Status/Fehler/Zeitstempel aktualisieren |

### POST `/api/v1/integration-sync-states` (Upsert)

```json
{
  "syncable_type": "App\\Models\\Client",
  "syncable_id": 1,
  "provider": "moco",
  "status": "synced",
  "external_id": "12345",
  "last_synced_at": "2026-05-14T12:00:00Z",
  "last_error": null,
  "meta": { "invoice_id": "INV-1" }
}
```

Erlaubte `syncable_type`-Werte: `App\Models\Client`, `App\Models\Project`, `App\Models\ProjectDomain`, `App\Models\CostLineItem`.

`provider`: `moco` | `managewp` | `autodns`  
`status`: Werte wie bei `MocoSyncStatus` (`pending`, `synced`, `failed`, `skipped`).

## Webhooks (extern → Laravel → n8n optional)

**URL:** `POST /api/v1/webhooks/{integration}`  
`integration` ∈ `moco` | `managewp` | `autodns`

### Beispiel-Payloads (flexibel)

Die Services lesen typischerweise eines der Felder `external_id`, `customer_id`, `id` (Moco), `site_id` (ManageWP), `zone_id` (AutoDNS).

**Moco (Kunde oder Projekt anhand externer ID markieren):**

```json
{ "customer_id": "moco-42", "event_id": "evt-2026-001" }
```

**ManageWP:**

```json
{ "site_id": "123456", "event_id": "mwp-001" }
```

**AutoDNS:**

```json
{ "zone_id": "98765", "event_id": "dns-001" }
```

**Verhalten:** Es werden passende Datensätze gesucht (`moco_customer_id`, `moco_project_id`, `managewp_site_id`, `autodns_id`). Für jeden Treffer wird ein Eintrag in `integration_sync_states` mit `status: pending` angelegt/aktualisiert (Signal an n8n: „bitte synchronisieren“).

## Externe IDs im Datenmodell

| System | Speicherort |
|--------|-------------|
| **Moco** | `clients.moco_customer_id`, `projects.moco_project_id`, `cost_line_items.moco_sync_status` |
| **ManageWP** | `projects.managewp_site_id`, optional `maintenance_histories.managewp_reference` |
| **AutoDNS** | `project_domains.autodns_id` |

Unique-Indizes auf kritischen Spalten sind in den Migrationen bereits berücksichtigt (Kollisionen bei Dubletten vermeiden).

## Sicherheitskonzept (Kurz)

1. **TLS:** Nur HTTPS in Produktion; Webhook-URLs in n8n auf `https://…` setzen.
2. **Geheimnisse:** Bearer- und HMAC-Secrets nur in `.env`/Secret-Store; niemals in Workflows hardcoden (n8n Credentials).
3. **Least privilege:** Dedizierten API-User + Token nur mit `integrations`; Token rotieren und bei Verdacht widerrufen (`personal_access_tokens` löschen).
4. **Webhooks:** IP-Allowlisting nur wenn statische Ausgang-IPs (selten bei n8n Cloud) – sonst starkes Secret + HMAC über **rohen** Body.
5. **Idempotenz:** `X-Webhook-Dedupe` / `event_id` nutzen, um Doppelverarbeitung zu vermeiden.
6. **Logging:** `integration_webhook_events` enthält Payloads – Zugriff auf DB/Backups absichern.

## Empfohlene n8n-Workflows

### 1) „Outbound: Moco-Kunde anlegen/aktualisieren“

1. **Trigger:** Manuell, Schedule oder Webhook aus internem Tool.
2. **HTTP Request** → Moco API (extern).
3. **HTTP Request** → `PATCH /api/v1/clients/{id}` mit `moco_customer_id`.
4. **HTTP Request** → `POST /api/v1/integration-sync-states` mit `status: synced`, `last_synced_at`.

### 2) „Inbound: Moco Webhook → Queue für Sync“

1. **Webhook** (n8n empfängt Moco-Event) **oder** direkt Moco → Laravel `POST /api/v1/webhooks/moco`.
2. Laravel setzt `integration_sync_states` auf `pending`.
3. n8n **pollt** `GET /api/v1/integration-sync-states?provider=moco&status=pending` (Cron alle 2–5 Min).
4. Verarbeitung: Moco lesen, lokale Felder prüfen, danach `PATCH` auf Sync-State → `synced` oder `failed` + `last_error`.

### 3) „ManageWP Site-ID zu Projekt“

1. ManageWP API: Site-Liste / Site-Detail.
2. `PATCH /api/v1/projects/{id}` mit `managewp_site_id`.
3. Optional `POST /api/v1/integration-sync-states` für Provider `managewp`.

### 4) „AutoDNS Zone ↔ Domain“

1. AutoDNS API: Zone-Events oder geplanter Abgleich.
2. `PATCH /api/v1/project-domains/{id}` mit `autodns_id`.
3. Webhook `POST /api/v1/webhooks/autodns` bei Zone-Änderungen → pending → n8n-Folgejob.

### 5) „Kostenposition Moco-Sync“

1. Nach erfolgreicher Moco-Rechnung: `PATCH /api/v1/cost-line-items/{id}` mit `moco_sync_status: synced`.
2. Bei Fehler: `failed` + `notes` oder `meta` über Sync-State.

## Code-Referenz (Projekt)

- Routen: `routes/api.php`
- Controller: `app/Http/Controllers/Api/V1/`
- Webhook-Middleware: `app/Http/Middleware/VerifyWebhookSignature.php`
- Webhook-Logik: `app/Services/Integrations/IntegrationWebhookService.php`
- Modelle: `IntegrationSyncState`, `IntegrationWebhookEvent`; Trait `App\Models\Concerns\HasIntegrationSyncStates`
- Konfiguration: `config/integrations.php`

## Tests

`php artisan test tests/Feature/Api/N8nIntegrationTest.php` prüft Sanctum-Abilities, Webhook-Bearer und Moco→Client→Sync-State.
