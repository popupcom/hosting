#!/usr/bin/env bash
# Überträgt die lokale SQLite-Datenbank auf den Live-Server (ersetzt database/database.sqlite).
#
# Voraussetzungen:
#   - SSH-Zugang zum Server
#   - Live nutzt ebenfalls SQLite (wie deploy.sh / Standard-Setup)
#   - Code auf Live ist deployt (git pull), Migrationen können lokal schon in der DB stehen
#
# Nutzung:
#   LIVE_SSH="user@server.example" ./scripts/push-database-to-live.sh
#   LIVE_SSH="ssh-233313-hosting@hosting.popup.gmbh" LIVE_PATH="hosting" ./scripts/push-database-to-live.sh
#
# WARNUNG: Überschreibt die komplette Live-Datenbank.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOCAL_DB="${LOCAL_DB:-$ROOT/database/database.sqlite}"
LIVE_SSH="${LIVE_SSH:-ssh-233313-hosting@hosting.popup.gmbh}"
LIVE_PATH="${LIVE_PATH:-hosting}"
REMOTE_DB="${LIVE_PATH}/database/database.sqlite"
PHP="${LIVE_PHP:-/usr/bin/php84}"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"
LOCAL_BACKUP="$ROOT/storage/app/backups/database-local-$TIMESTAMP.sqlite"

if [[ -z "$LIVE_SSH" ]]; then
  echo "Fehler: LIVE_SSH ist nicht gesetzt."
  echo 'Beispiel: LIVE_SSH="user@ihr-server.tld" ./scripts/push-database-to-live.sh'
  exit 1
fi

if [[ ! -f "$LOCAL_DB" ]]; then
  echo "Fehler: Lokale Datenbank nicht gefunden: $LOCAL_DB"
  exit 1
fi

mkdir -p "$ROOT/storage/app/backups"
cp "$LOCAL_DB" "$LOCAL_BACKUP"
echo "Lokales Backup: $LOCAL_BACKUP"

echo "Live-Backup auf dem Server erstellen …"
ssh "$LIVE_SSH" "mkdir -p ${LIVE_PATH}/storage/app/backups && test -f ${REMOTE_DB} && cp ${REMOTE_DB} ${LIVE_PATH}/storage/app/backups/database-live-before-${TIMESTAMP}.sqlite || true"

echo "Wartungsmodus auf Live …"
ssh "$LIVE_SSH" "cd ${LIVE_PATH} && ${PHP} artisan down || true"

echo "Datenbank übertragen …"
scp "$LOCAL_DB" "${LIVE_SSH}:${REMOTE_DB}"

echo "Caches leeren & optimieren …"
ssh "$LIVE_SSH" "cd ${LIVE_PATH} && ${PHP} artisan migrate --force && ${PHP} artisan optimize:clear && ${PHP} artisan optimize"

echo "Wartungsmodus beenden …"
ssh "$LIVE_SSH" "cd ${LIVE_PATH} && ${PHP} artisan up"

echo "Fertig. Live-Datenbank wurde aus lokaler Kopie ersetzt."
