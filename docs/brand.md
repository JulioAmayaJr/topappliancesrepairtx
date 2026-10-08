# Top Appliances Repair — Brand tokens

Source of truth for every color, type and spacing token in `wp-content/themes/top-appliance/theme.json`.
All brand colors were **extracted** from the live site (https://topappliancesrepairtx.com) on 2026-10-07;
nothing here is invented. Derived tones are only lighter/darker mixes of a brand color, or neutrals,
and each one documents its formula.

Extraction scripts: `docs/research/colors.py` (logo pixel counts + CSS frequency) and
`docs/research/clusters.py` (logo pixels split by region/hue). Run with Pillow:
`python colors.py <download-dir>`.

## 1. Brand colors (exact, extracted)

| Token | Hex | Role | Where it came from |
|---|---|---|---|
| `gear-deep` | `#054199` | Primary dark — headings, wordmark | `logo-desktop.png`: wordmark "TOP APPLIANCES REPAIR" is 681/734 px exactly `#054199`; darkest stop of the gear gradient in all 3 logos |
| `brand-blue` | `#054E99` | **Primary** — links, buttons, icons | Current CSS (inline Divi styles): most used brand color, 45 occurrences — `.headingH1 h1 {color:#054e99}`, `.blue-bold span`, header button `et_pb_button_1_tb_header {background:#054e99}`. Also the gear's mid-gradient (`#055099` is the single most common logo pixel) |
| `gear-light` | `#055C99` | Light end of gear gradient (seal gradient only) | `logo-desktop.png` / `logo-wheel.png`: lightest blue pixel cluster of the gear (`#054199 → #055C99` is the logo gradient) |
| `blue-bright` | `#0652A0` | Gradient start for dark bands | Current CSS: footer & section 4 `linear-gradient(180deg,#0652a0 0%,#013469 100%)` |
| `navy` | `#013469` | **Secondary** — dark sections, footer | Current CSS: same footer gradient end stop (4 occurrences) |
| `cyan` | `#29ABE2` | **Accent** — power-icon cyan; decorative, and text only on dark | `logo-desktop.png` power icon: dominant cyan pixel (89 px); CSS `.contact-form .wpcf7-submit {background:#29abe2}`, `.custom-testimonial h2`, mobile menu background (8 occurrences) |
| `spark` | `#0BE8F7` | Accent highlight on dark backgrounds (hover, active) | Current CSS: nav hover/current item and `.home h2.text-blue` (10 occurrences); matches the logo's brightest power-icon highlight `#0AEBF8` |
| `mist` | `#7EBEC5` | Muted accent on dark (secondary text on navy) | Current CSS: menu/search/hamburger icon color (16 occurrences) |
| `signal-red` | `#FF0000` | Urgent action color (Call) | Current CSS: header "Call" button `et_pb_button_0_tb_header {background:#ff0000}` and YouTube icon |
| `white` | `#FFFFFF` | Base | Current CSS: 114 occurrences; `footer-logo.png` is 100% `#FFFFFF` |
| `black` | `#000000` | — (reference only) | Current CSS: 38 occurrences |
| `gray-ee` | `#EEEEEE` | — (reference only) | Current CSS: 3 occurrences |

Excluded on purpose: `#ff6900 #cf2e2e #00d084 #9b51e0 #fcb900 #0693e3 #32373c #020381 #2874fc #313131`
are WordPress core preset palette variables printed by WP on every site, not brand colors. `#225a9c` is
only the Facebook icon. Divi framework defaults (`#2ea3f2`, `#666`, `#333`…) come from `Divi/style.css`
and are not brand colors.

## 2. Derived tones (documented mixes of brand colors)

| Token | Hex | Formula | Use | Contrast |
|---|---|---|---|---|
| `navy-900` | `#01274F` | `navy` + 25% black | Top bar, footer base | white 14.95:1 |
| `ink` | `#00172F` | `navy` + 55% black | Body/heading text | on white 18.05:1 |
| `slate` | `#39618B` | `navy` + 22% white | Secondary text | on white 6.45:1, on `tint-100` 5.46:1 |
| `tint-50` | `#F5F8FB` | white + 4% `brand-blue` | Alternate section background | — |
| `tint-100` | `#E6EDF5` | white + 10% `brand-blue` | Tiles, inputs, chips | `brand-blue` on it 6.96:1 |
| `line` | `#CDDCEB` | white + 20% `brand-blue` | Borders, dividers | — |
| `red-600` | `#D60000` | `signal-red` + 16% black | Call buttons (white text) | white 5.44:1 (pure `#FF0000` is only 4.0:1 → fails AA) |
| `red-700` | `#B20000` | `signal-red` + 30% black | Call button hover/active | white 7.26:1 |
| `cyan-700` | `#196A8C` | `cyan` + 38% black | Cyan when it must sit on white (icons, small text) | on white 6.03:1 |
| `cyan-100` | `#E1F3FB` | white + 14% `cyan` | Icon wells | — |

## 3. Allowed pairings (WCAG AA)

- Text on white/tint: `ink`, `gear-deep` (9.45:1), `brand-blue` (8.22:1), `slate`, `cyan-700`, `red-600`.
- Text on `navy`/`navy-900`: white, `spark` (8.19 / 9.9:1), `mist` (5.93 / 7.16:1), `cyan` (4.73 / 5.71:1).
- Never: `cyan` or `spark` text on white (2.62:1 / ~1.4:1); white text on `cyan`; white text on pure `#FF0000`.

## 4. Typography

- **Archivo** (variable; axes `wdth` 100–125, `wght` 400–800), self-hosted woff2, Latin subset (47 KB),
  `font-display: swap`, preloaded. One family, two voices:
  - Display: `font-stretch: 125%` (Expanded), weight 800, tight tracking — the wide, sturdy lettering of a
    service van door or an appliance rating plate.
  - Text: `font-stretch: 100%`, weight 400/600.
- The current site uses Open Sans (Divi default); typography was not part of the brand-color rule, so it
  was chosen to fit the subject (trade, sturdy, legible at small sizes).
- Fluid scale (≈1.25 ratio on mobile → 1.333 on desktop), via `clamp()` in `theme.json`.

## 5. Shape, depth, spacing

- Radius: `sm 6px` (inputs, chips), `md 12px` (tiles), `lg 20px` (media, panels), `pill 999px` (buttons).
- Shadows are tinted with `navy`, never neutral gray: `sm 0 1px 2px rgb(1 52 105 / .08)`,
  `md 0 8px 24px -8px rgb(1 52 105 / .22)`, `lg 0 24px 48px -16px rgb(1 52 105 / .30)`.
- Spacing scale (fluid): 20 → 80 in 7 steps (`0.5rem` … `clamp(4rem, 8vw, 7.5rem)`).

## 6. Design direction

- **Motif: the gear + power button from the logo.** Spent in one place: the 90-day warranty seal is a
  gear-shaped stamp in the exact logo gradient (`#054199 → #055C99`) with the cyan power ring. Everything
  around it stays quiet.
- Service tiles behave like appliance control-panel keys: the small power "LED" (`cyan`/`spark`) lights on
  hover/focus.
- Light, cool page (white / `tint-50`) with `navy` bands for trust bar, process and footer.
- Red is reserved for one job: calling. Blue is for booking. That rule holds on every page.
- No all-caps eyebrows, no numbered markers except the real 3-step process.
