# Altijara Theme (`altijara-theme`)

Native WordPress theme for **مجلة التجارة (Altijara Magazine)** — Jeddah Chamber of Commerce — built by **O2Nations** per the *O2 Nations Altijara Magazine Transformation Plan* (O2N-ENG-DIR-2026).

This theme replaces the heavy JNews + page-builder stack with a lightweight, **server-side rendered (SSR)** magazine platform. Editors configure homepage sections, sources, and layouts from WP Admin without writing PHP or using visual builders.

The static HTML/CSS/JS prototype at the repository root remains for visual comparison — **do not delete it**.

---

## What this theme is

| Goal | Plan target |
|------|-------------|
| Platform | WordPress native SSR (not a disconnected static frontend) |
| Scope | All **9** prototype surfaces as production templates |
| Editorial control | Full CMS control of section sources, counts, layouts — zero hardcoded category/post IDs in loops |
| Frontend stack | Pure CSS Grid/Flexbox + Vanilla JS — no Bootstrap, no Tailwind runtime, no jQuery on the public frontend |
| SEO continuity | Preserve `/%postname%/` permalinks and Rank Math / OpenGraph compatibility |
| Performance (theme side) | Unified CSS/JS, self-hosted fonts, query/transient caching; designed for LiteSpeed / Core Web Vitals targets |

---

## Architectural principles (theme)

1. **Isolated namespace** — Lives at `wp-content/themes/altijara-theme/` until cutover; production stays on JNews until then.
2. **Decoupled content** — Homepage and archive sections read category, tag, count, offset, sort, layout, and manual picks from Theme Options / Section Manager — not hardcoded IDs.
3. **Zero-dependency frontend** — One primary stylesheet and deferred modular JS; self-hosted WOFF2 fonts (`font-display: swap`).
4. **SSR first** — Crawlers and social previews receive real HTML (title, body, OpenGraph), not client-only fetches.
5. **Cache-aware queries** — Section queries use transient caching and efficient `WP_Query` patterns (`no_found_rows` where appropriate), with per-section cache purge for editors.

---

## The 9 theme surfaces

| Prototype | WordPress template | Theme responsibility |
|-----------|-------------------|----------------------|
| Homepage | `front-page.php` / home partials | Modular sections + Section Manager–driven SSR |
| Category / sector | `category.php` | Hero, subcategory filters, grid/list, pagination / load more |
| Article | `single.php` | Metadata, reading UX, related posts, print styles, ad slots |
| Issues library | `archive-issue.php`, `single-issue.php` | `issue` CPT archive, filters, flipbook / PDF presentation |
| Login / members | `page-login.php` | Branded frontend auth UI (theme templates) |
| Subscribe | `page-subscribe.php` | Subscription tiers presentation & CTA |
| Advertise | `page-advertise.php` | Media kit / rate-card UI & lead capture |
| About | `page-about.php` | Heritage, masthead, institutional story |
| Contact | `page-contact.php` | Departmental contact routing UI |

Supporting routes: `search.php`, `404.php`, `page.php`, `index.php`, plus shared `header.php` / `footer.php` and `template-parts/`.

---

## Homepage engine

The homepage is an **orchestrator** of modular SSR sections (not a page-builder shortcode page), including:

- Breaking news ticker (cache-bypass / ESI-friendly where available)
- Hero composite (lead story, magazine cover slider, Chamber / opinion column)
- Follow-ups & reports grid
- Sector splits (finance/business, real estate, healthcare, tourism)
- Automotive feature block
- Technology hub + **Most Read** widget
- Magazine issues slider (`issue` CPT)
- Subscription / VIP callout strip
- Global footer (menus, taxonomy directory, newsletter, social)

Partials live under `template-parts/home/`; chrome under `template-parts/header/` and `template-parts/footer/`.

---

## Section Manager & CMS controls

Editors manage sections via the **Altijara Section Manager** (and related Theme Options). Each section exposes a standard schema:

| Control | Purpose |
|---------|---------|
| Enable / disable | Show or hide a section without breaking layout |
| Arabic title | Reader-facing section heading |
| Data source | Category, tag, manual post IDs, most-read, sticky pick |
| Category / tag picker | Live taxonomy selection |
| Manual post override | AJAX search to pin specific articles |
| Count & offset | Cardinality and de-duplication across blocks |
| Sort order | Date, comments, views, random |
| Layout variant | Grid, split, hero+list, etc. |
| Cache purge | Flush that section’s transient immediately |

**Planned section IDs (control matrix):**  
`sec_hero_lead`, `sec_hero_sub`, `sec_chamber`, `sec_cards_grid`, `sec_split_1_a`, `sec_split_1_b`, `sec_cars`, `sec_tech`, `sec_most_read`, `sec_split_2_a`, `sec_split_2_b`, `sec_issues_slider`.

Reusable homepage widgets are registered under `inc/widgets/` (registry, query engine, renderer, admin AJAX).

---

## Template capabilities (by area)

### Category archives (`category.php`)
- Dynamic category hero
- AJAX subcategory filter tabs
- Grid ↔ list layout switcher (preference in `localStorage`)
- Numbered pagination for SEO + “Load more” for readers
- In-feed ad slot placeholders
- Breadcrumbs / Rank Math–compatible archive markup

### Single article (`single.php`)
- Arabic dates, reading time, view counter
- Author byline / credentials
- Social share drawer
- Programmatic in-article ad injection (e.g. after paragraphs 3 and 7)
- Related articles (tags → category → staff picks)
- Print stylesheet for clean boardroom output

### Issues archive (`issue` CPT)
- Meta: issue number, release date text, PDF URL, flipbook URL
- Year / decade filtering
- Flipbook modal + PDF download presentation (access rules as configured)

### Static / marketing page templates
Theme provides branded shells for login, subscribe, advertise, about, and contact. Payment gateways, OAuth providers, SMTP, and WAF are **site/infrastructure** concerns wired to these templates — not the theme’s sole responsibility.

---

## Theme performance design

- **Assets:** Versioned enqueue via `inc/enqueue.php`; unified CSS + deferred Vanilla JS modules under `assets/`
- **Fonts:** Self-hosted WOFF2 (design tokens / Design tab in Theme Options)
- **Queries:** Shared helpers and caching in `inc/queries.php`, `inc/caching.php`
- **Images:** Featured images, lazy-load, srcset/fallbacks via media helpers and card components
- **Targets (site + theme):** Sub-400ms TTFB and 90+ mobile PageSpeed when paired with LiteSpeed / Redis / optimized media — theme keeps payload lean so those targets are achievable

---

## Implementation phases (theme workstreams)

| Phase | Focus |
|-------|--------|
| **1** | Theme foundation: setup, menus, enqueue, tokens, RTL/typography, header/footer, helpers, query cache |
| **2** | Homepage SSR + Section Manager + modular widgets |
| **3** | Editorial templates: `single.php`, `category.php`, related/enrichment, print, archive UX |
| **4** | `issue` CPT, archive/single issue UI, filters, flipbook/PDF presentation |
| **5** | Theme page templates & UI for auth, subscribe, advertise, about, contact (integrations configured on the site) |

Ops items in the plan (DB JNews cleanup SQL, production cutover, rollback CLI, plugin decommissioning) are **outside** this theme package and documented in the project plan / tracker.

Full task checklist: `docs/Altijara_Transformation_Interactive_Project_Tracker.xlsx`  
Source plan: `docs/O2Nations_Altijara_Magazine_Transformation_Plan.pdf`

---

## Install (staging)

1. Upload `altijara-theme/` to `wp-content/themes/`
2. Appearance → Themes → activate **Altijara Theme**
3. Settings → Permalinks → Save (flush rewrite rules for `issue` CPT)
4. Create pages and assign templates where needed:
   - من نحن → About
   - اتصل بنا → Contact
   - أعلن معنا → Advertise
   - اشترك → Subscribe
   - تسجيل الدخول → Login
5. **Altijara Theme** admin → Section Manager / Theme Options: map section sources to category slugs
6. Reading → Front page displays: latest posts (uses `front-page.php`)

---

## Directory map

```
altijara-theme/
├── style.css                 # Theme header
├── functions.php             # Bootstrap includes
├── front-page.php            # Homepage orchestrator
├── category.php / single.php
├── archive-issue.php / single-issue.php
├── page-*.php                # About, contact, advertise, subscribe, login
├── header.php / footer.php
├── inc/
│   ├── setup.php, enqueue.php, helpers.php, caching.php, queries.php
│   ├── section-manager.php, theme-options.php, design.php, defaults.php
│   ├── activation.php, nav.php, social.php
│   └── widgets/              # Registry, query engine, renderer, AJAX
├── template-parts/
│   ├── home/                 # Homepage section partials
│   ├── header/ / footer/     # Site chrome
│   └── cards/
├── assets/css|js|fonts|images
└── docs/                     # Plan PDF + project tracker
```

---

## Out of scope for this package

The transformation plan also covers production hosting, LiteSpeed/Redis ops, Cloudflare WAF, payment/OAuth credentials, CRM/SMTP webhooks, JNews database purge scripts, and the 60-second theme cutover/rollback playbook. Those are **site operations**, not theme source. This README describes only what **`altijara-theme`** is designed to deliver.
