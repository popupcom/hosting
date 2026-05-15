# API-Token für n8n (Laravel Sanctum)

Sanctum ist im Projekt installiert (`laravel/sanctum`). n8n authentifiziert sich per **Bearer-Token** im Header `Authorization`.

## 1. Token mit Berechtigung `integrations` erzeugen

Auf dem Server (oder lokal mit `php artisan tinker`):

```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'office@popup.at')->first();
$user->createToken('n8n', ['integrations'])->plainTextToken;
```

Der ausgegebene **Plaintext-Token** wird nur einmal angezeigt — sicher speichern (n8n Credentials, Passwort-Manager).

## 2. n8n HTTP Request Node

- **URL:** z. B. `https://deine-domain.tld/api/projects`
- **Authentication:** Header Auth  
  - Name: `Authorization`  
  - Value: `Bearer <DEIN_TOKEN>`
- Optional: Header `Accept: application/json`

## 3. Geschützte Beispiel-Endpunkte (ohne `/v1`)

| Methode | Pfad | Beschreibung |
|--------|------|----------------|
| GET | `/api/projects` | Projektliste (paginiert, wie `GET /api/v1/projects`) |
| POST | `/api/maintenance-logs` | Neues Wartungsprotokoll anlegen (JSON-Body, siehe unten) |

Beide Routen erfordern: **`auth:sanctum`** + Ability **`integrations`** + Rate-Limit **`api`**.

### POST `/api/maintenance-logs` (JSON)

```json
{
  "project_id": 1,
  "maintenance_type": "wordpress_core",
  "performed_by": "n8n",
  "performed_on": "2026-05-14",
  "result": "Core aktualisiert auf 6.8.",
  "has_errors": false,
  "notes": null,
  "managewp_reference": null
}
```

Erlaubte `maintenance_type`-Werte: `wordpress_core`, `plugin_update`, `theme_update`, `backup`, `performance_check`, `security_check`.

Antwort: **HTTP 201** mit Ressource unter `data`.

## 4. Weitere API-Routen

Versionierte Integration-API: **`/api/v1/...`** (siehe [n8n-api-and-webhooks.md](./n8n-api-and-webhooks.md)).

## 5. Token widerrufen

In Tinker: `$user->tokens()->delete();` oder einzelnes Token in Tabelle `personal_access_tokens` löschen.
