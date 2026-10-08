#!/usr/bin/env bash
# Export DB + uploads to ./backups (timestamped). Usage: bin/export.sh [--url https://prod.example.com]
set -euo pipefail
cd "$(dirname "$0")/.."
set -a; . ./.env; set +a
STAMP=$(date +%Y%m%d-%H%M%S)
TARGET_URL=""
[ "${1:-}" = "--url" ] && TARGET_URL="${2:?missing url}"
wp() { docker compose run --rm -q -T wpcli "$@"; }
if [ -n "$TARGET_URL" ]; then
  # search-replace into the export only; the local DB is untouched
  wp search-replace "$WP_URL" "$TARGET_URL" --all-tables --export="/project/backups/db-$STAMP.sql" --report-changed-only
else
  wp db export "/project/backups/db-$STAMP.sql" --add-drop-table
fi
gzip "backups/db-$STAMP.sql"
docker compose exec -T wordpress tar -C /var/www/html/wp-content -czf - uploads > "backups/uploads-$STAMP.tar.gz"
echo "✓ backups/db-$STAMP.sql.gz"
echo "✓ backups/uploads-$STAMP.tar.gz"
