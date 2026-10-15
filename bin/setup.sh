#!/usr/bin/env bash
# Provision the local WordPress install. Safe to re-run (idempotent).
# Usage: bin/setup.sh            full install + configuration + seed content
#        bin/setup.sh --no-seed  install/configure only
set -euo pipefail
cd "$(dirname "$0")/.."

[ -f .env ] || { echo "Missing .env — copy .env.example to .env first."; exit 1; }
set -a; . ./.env; set +a

wp() { docker compose run --rm -q -T wpcli "$@"; }

echo "→ Waiting for database…"
for i in $(seq 1 30); do wp db check >/dev/null 2>&1 && break; sleep 2; done

if ! wp core is-installed >/dev/null 2>&1; then
  echo "→ Installing WordPress"
  wp core install --url="$WP_URL" --title="$WP_TITLE" \
    --admin_user="$WP_ADMIN_USER" --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" --skip-email
fi

echo "→ Language, timezone, permalinks"
wp language core install en_US --activate >/dev/null 2>&1 || true
wp option update WPLANG ''
wp option update timezone_string 'America/Chicago'
wp option update date_format 'F j, Y'
wp option update start_of_week 0
wp rewrite structure '/%postname%/' --hard
wp option update blogdescription 'Same-day appliance repair in Houston, TX'
# 1 = indexable (local/production), 0 = noindex (staging). Set BLOG_PUBLIC in .env.
wp option update blog_public "${BLOG_PUBLIC:-1}"

echo "→ Comments off everywhere"
wp option update default_comment_status closed
wp option update default_ping_status closed
wp option update default_pingback_flag 0
wp option update comment_registration 1
wp option update show_avatars 0

echo "→ Remove sample content and plugins"
wp post delete $(wp post list --post_type=post,page --name='hello-world' --format=ids) --force 2>/dev/null || true
wp post delete $(wp post list --post_type=page --name='sample-page' --format=ids) --force 2>/dev/null || true
wp post delete $(wp post list --post_type=page --post_status=draft --name='privacy-policy' --format=ids) --force 2>/dev/null || true
wp comment delete $(wp comment list --format=ids) --force 2>/dev/null || true
wp plugin delete akismet hello 2>/dev/null || true
wp theme delete twentytwentythree twentytwentyfour 2>/dev/null || true

echo "→ Plugins: Rank Math, Fluent Forms, FluentSMTP"
wp plugin install seo-by-rank-math fluentform fluent-smtp --activate
wp plugin activate top-appliance-core 2>/dev/null || echo "  (top-appliance-core not present yet)"
wp theme activate top-appliance 2>/dev/null || echo "  (top-appliance theme not present yet)"

if wp plugin is-active top-appliance-core >/dev/null 2>&1; then
  echo "→ Configure plugins and content"
  wp eval-file /project/bin/configure.php
  if [ "${1:-}" != "--no-seed" ]; then
    wp eval-file /project/bin/seed.php
  fi
  wp rank-math sitemap generate >/dev/null 2>&1 || true
fi

wp rewrite flush --hard
wp cache flush >/dev/null
echo "✓ Done → $WP_URL  (admin: $WP_URL/wp-admin, user: $WP_ADMIN_USER)"
echo "  Mailpit → http://localhost:${MAILPIT_UI_PORT:-8027}"
