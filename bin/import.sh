#!/usr/bin/env bash
# Import a DB dump (and optionally uploads) into the local site.
# Usage: bin/import.sh backups/db-XXXX.sql.gz [backups/uploads-XXXX.tar.gz] [--from-url https://old.example.com]
set -euo pipefail
cd "$(dirname "$0")/.."
set -a; . ./.env; set +a
DB="${1:?db dump required}"; UP="${2:-}"; FROM=""
[ "${3:-}" = "--from-url" ] && FROM="${4:?missing url}"
[ "${2:-}" = "--from-url" ] && { UP=""; FROM="${3:?missing url}"; }
wp() { docker compose run --rm -q -T wpcli "$@"; }
TMP="backups/.import.sql"
case "$DB" in *.gz) gunzip -c "$DB" > "$TMP";; *) cp "$DB" "$TMP";; esac
wp db import /project/backups/.import.sql && rm -f "$TMP"
[ -n "$UP" ] && docker compose exec -T wordpress tar -C /var/www/html/wp-content -xzf - < "$UP"
[ -n "$FROM" ] && wp search-replace "$FROM" "$WP_URL" --all-tables --report-changed-only
wp rewrite flush --hard && wp cache flush
echo "✓ Imported"
