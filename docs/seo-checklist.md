# SEO, performance & accessibility checklist — Home

Verified on 2026-10-08, re-checked on 2026-10-09 after the compact-scale / brands / reviews / footer update against the local build (`http://localhost:8080/`, WordPress 7.1.3, PHP 8.3).
Re-run everything with `npm run shots`, `npm run lighthouse` and `npm run jsonld`.

## Lighthouse (lighthouse@12, Chrome for Testing 149)

| Run | Performance | Accessibility | Best Practices | SEO | LCP | CLS | TBT | FCP |
|---|---|---|---|---|---|---|---|---|
| Mobile (Moto G Power emulation, slow 4G, 4× CPU) | **99** | **100** | **100** | **100** | 1.9 s | 0.005 | 10 ms | 1.0 s |
| Mobile, 2026-10-09 update | **99** | **100** | **100** | **100** | 1.9 s | 0.005 | — | — |
| Desktop | **100** | **100** | **100** | **100** | 0.4 s | 0 | 0 ms | 0.3 s |

Remaining non-scored notices: DOM size (~1,270 nodes, mostly the duplicated desktop/mobile menus and
inline SVG icons) and the network chain to the idle-loaded Motion chunk. The mobile hero image is
intentionally eager (`fetchpriority="high"`) even though it sits below the fold on phones (brief requirement).

INP is a field metric (not measurable in a Lighthouse navigation run). Lab proxy: TBT 10 ms; all
interaction handlers are tiny and the only third-party JS (Fluent Forms + jQuery) is deferred.

## Structure & semantics
- [x] One `<h1>` per page (Home + every inner template checked: services, brands, areas, error codes, blog, pages, 404, search)
- [x] Heading order H1 → H2 → H3 → H4 with no skipped levels; footer column titles are H2
- [x] Landmarks: `<header>` (template part), `<nav aria-label>` (main, mobile, footer columns), `<main id="main">`, `<section>`s, `<footer>`
- [x] Skip link is the first tab stop and jumps to `#main`
- [x] Descriptive internal links ("Refrigerator repair", "See all brands we service", "Read all repair tips on the blog"); no "click here / read more"
- [x] Real alt text on every image; decorative SVG icons are `aria-hidden`

## Rank Math (titles, meta, social, sitemap, robots, redirects)
- [x] Title: `Same-Day Appliance Repair in Houston, TX | Top Appliances Repair`
- [x] Meta description (153 chars), canonical, robots `index, follow, max-image-preview:large`
- [x] Open Graph + Twitter card with a 1200×630 **PNG** image (AVIF is not accepted by social networks)
- [x] XML sitemap index: posts, pages, services, brands, service areas (error codes appear once they have posts); attachments/authors off
- [x] robots.txt points to the sitemap
- [x] Redirections module on — the 301 map is in `docs/url-inventory.csv` (`action = 301`), to load before launch
- [x] Schema module (`rich-snippet`) **off** + `rank_math/json_ld` filtered empty → no duplicate JSON-LD
- [x] Title templates per post type (e.g. `Appliance Repair in %title%, TX`) ready for the 120 pages

## JSON-LD (top-appliance-core) — `npm run jsonld`
- [x] Exactly one JSON-LD block, valid JSON
- [x] `HomeAndConstructionBusiness`: name, url, telephone, email, PostalAddress, GeoCoordinates, openingHoursSpecification (Mon–Fri 09–17, Sat 09–15), areaServed (15 cities), sameAs (Facebook, Instagram, YouTube, Google Business Profile), logo, image, hasOfferCatalog (11 services)
- [x] `WebSite` (+ SearchAction), `WebPage`, `BreadcrumbList`
- [x] `FAQPage` generated from the visible `<details>` FAQ (7 Q&As) — edits in the editor update the schema
- [x] No `aggregateRating`, no `Review` markup
- [x] NAP identical: the phone in schema matches all 9 `tel:` links; address/hours come from Business Info only

## Core Web Vitals techniques
- [x] Hero image: `fetchpriority="high"`, no lazy loading, `width`/`height`, `srcset` + accurate `sizes`
- [x] Every other image: `loading="lazy"`, `decoding="async"`, `srcset`/`sizes`, intrinsic size; AVIF sub-sizes (WebP originals)
- [x] Fonts: Archivo variable self-hosted, woff2, Latin subset (28.7 KB), `preload`, `font-display: swap`, metric-matched fallbacks (`size-adjust`, ascent/descent overrides) → no swap shift
- [x] Critical CSS inlined (tokens, header, hero, trust bar, first section); the rest loads non-blocking
- [x] Only the CSS of blocks actually used is printed (separate core block assets, inlined)
- [x] Emoji script/styles, oEmbed, wp-embed, jquery-migrate, comment feeds, generator tags removed
- [x] Zero render-blocking JS: theme JS is an ES module; Fluent Forms + jQuery are `defer`
- [x] Animations: Motion (`animate` + `inView`) in a lazy chunk loaded on idle; never on the hero/LCP; opacity/transform only; skipped entirely with `prefers-reduced-motion`
- [x] Map facade: static SVG map; the Google Maps iframe loads only after a click
- [x] Long cache headers on static assets (local Apache `mod_expires`; replicate in production)

## Accessibility (WCAG 2.2 AA)
- [x] Contrast: every text/background pair checked (see `docs/brand.md` §3); red buttons use `#D60000` (5.4:1) instead of `#FF0000` (4.0:1)
- [x] Visible focus ring everywhere (cyan 700 on light, spark on dark)
- [x] All buttons ≥ 48 px tall (checked programmatically at 390 px)
- [x] Labels on every form field (ZIP checker + booking form), errors announced inline, ZIP result in a `role="status"` live region
- [x] Mega menu: disclosure buttons with `aria-expanded`, Escape closes, works on hover/click; mobile menu uses the Popover API and `<details>` — both work with JavaScript disabled
- [x] Accessible name contains the visible label on every control (Lighthouse `label-content-name-mismatch` passes)

## Reviews widget
- [x] Google-style header (G logo, average rating + stars, total, "See all reviews on Google →") and an accessible carousel (prev/next buttons, swipe via scroll-snap, arrow keys, focusable track, "Read more" for clamped text)
- [x] Data: Settings → Business Info → Reviews (pinned review + list + average + total); `top_reviews_source` filter ready for a Google Places API sync
- [x] Linda L's real review is the pinned review, always shown first
- [x] Sample reviews ("Customer A"…, labelled "Preview — sample reviews") appear only when `wp_get_environment_type()` is local/development or an admin is logged in; in production without data only Linda L + the Google link are shown
- [x] No reviews and no aggregateRating in the JSON-LD

## Business data
- [x] Phone, address, hours, email, socials, ZIPs, review links: **only** in Settings → Business Info, read via helpers, dynamic blocks and the `top/business` block-bindings source
- [x] FAQ answers that mention hours/warranty are bound to Business Info too

## Placeholders to replace with real client data
| Where | What is needed |
|---|---|
| Hero image | Real photo of a technician at work, 4:3, ≥1600×1200 (today: brand gradient + logo wheel, same ratio) |
| "Why choose us" image | Technician/owner portrait, 4:5, ≥1200×1500 (today: brand block, same ratio) |
| 9 blog featured images | One photo per article, 16:9, ≥1200×675 (today: brand block) |
| Reviews | Real Google reviews (name, rating, date, text), average rating and total in Business Info → Reviews |
| Legal pages | Final Privacy Policy and Terms text (short drafts are published) |
| Blog article bodies | Full text of the 9 posts (currently excerpt + migration note) |
| Service / brand / area pages | Real copy (the "content pending" notes are visible to logged-in editors only) |
| Google reviews link | Confirm the Google Business Profile URL (taken from the current map embed, CID 7779343947408438677) |
| Geo coordinates | 29.6972365, −95.5878163 (OpenStreetMap geocode of the address) — verify against the GBP pin |
| Service ZIP codes | Per-city ZIP lists seeded from public USPS assignments (Houston = 77002–77099) — confirm the real service area |
| Email sender | Production SMTP/API in FluentSMTP (local uses Mailpit); recipient for booking notifications |
| Error codes | Content for the `error_code` post type (archive and appliance terms exist, no codes yet) |
