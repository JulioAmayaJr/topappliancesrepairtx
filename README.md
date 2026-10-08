# Top Appliances Repair — WordPress site

New WordPress site for Top Appliances Repair (Houston, TX). Replaces https://topappliancesrepairtx.com.

- Block theme: `wp-content/themes/top-appliance` (theme.json tokens, templates, template parts, one pattern per Home section)
- Core plugin: `wp-content/plugins/top-appliance-core` (CPTs, Business Info settings, JSON-LD, dynamic blocks)
- Plugins installed by wp-cli: Rank Math SEO, Fluent Forms, FluentSMTP. Nothing else.
- Docs: `docs/brand.md` (palette + where it came from), `docs/url-inventory.csv` (current URLs → new URLs), `docs/seo-checklist.md`

## Requirements

Docker Desktop, Node 18.20+ (for asset builds and checks), git.

## First run

```bash
cp .env.example .env          # then change the passwords
docker compose up -d          # WordPress :8080, MariaDB, Mailpit :8027
npm install && npm run build  # compile theme CSS/JS from src/ to assets/
bin/setup.sh                  # install WP, plugins, configure, seed content
```

- Site: http://localhost:8080 — admin at `/wp-admin` (user/password in `.env`)
- Mailpit (catches every email the site sends): http://localhost:8027

`bin/setup.sh` is idempotent. `bin/setup.sh --no-seed` configures without creating content.

## Day to day

| Task | Command |
|---|---|
| Start | `docker compose up -d` |
| Stop (keeps data) | `docker compose stop` |
| Stop and remove containers (keeps data volumes) | `docker compose down` |
| Wipe everything (DB + WP core volumes) | `docker compose down -v` |
| wp-cli | `bin/wp <command>` e.g. `bin/wp plugin list` |
| Watch assets | `npm run dev` |
| Build assets (minified) | `npm run build` |
| Screenshots 390px/1440px | `npm run shots` → `docs/screenshots/` |
| Lighthouse mobile + desktop | `npm run lighthouse` → `lighthouse/` |
| Validate JSON-LD | `npm run jsonld` |

## Export

```bash
bin/export.sh                                      # DB + uploads → backups/
bin/export.sh --url https://topappliancesrepairtx.com   # same, with URLs rewritten for production
```

## Import / restore

```bash
bin/import.sh backups/db-XXXX.sql.gz backups/uploads-XXXX.tar.gz
bin/import.sh backups/db-XXXX.sql.gz --from-url https://staging.example.com
```

## Migrating to production

1. Build assets: `npm run build` (the compiled `assets/` folder is committed, so the server does not need Node).
2. On the server: WordPress 6.8+ (tested on 7.1), PHP 8.3, MariaDB/MySQL.
3. Upload `wp-content/themes/top-appliance` and `wp-content/plugins/top-appliance-core`.
4. `bin/export.sh --url https://<production-domain>` and import the SQL + uploads on the server
   (`wp db import`, untar into `wp-content/`).
5. Install/activate Rank Math, Fluent Forms, FluentSMTP. In FluentSMTP, replace the Mailpit connection
   with the real SMTP/API provider.
6. Settings → Permalinks → Save (flushes rewrites). Check Settings → Reading → "Search engine visibility" is **off**.
7. Add the 301 redirects listed in `docs/url-inventory.csv` (`action = 301`) in Rank Math → Redirections.
8. Fill **Business Info** (Settings → Business Info): Google reviews URL/embed, verified geo coordinates.
9. Submit `https://<domain>/sitemap_index.xml` in Google Search Console.

## Where business data lives

Phone, address, hours, email, socials and ZIP codes are **never hardcoded**. Edit them in
**wp-admin → Settings → Business Info**. They feed the header, footer, CTAs, mobile call bar,
the ZIP checker and the JSON-LD schema. In the block editor, core blocks read them through the
`top/business` block-bindings source.

## Project layout

```
docker-compose.yml   .env.example   package.json   scripts/ (esbuild, screenshots, lighthouse, json-ld check)
bin/                 setup.sh, configure.php, seed.php, export.sh, import.sh, wp
seed/                placeholder images used by seed.php
docs/                brand.md, url-inventory.csv, seo-checklist.md, screenshots/, research/
wp-content/themes/top-appliance/     theme.json, templates/, parts/, patterns/, src/ → assets/
wp-content/plugins/top-appliance-core/  CPTs, settings, schema, blocks/
```
