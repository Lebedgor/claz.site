# CLAZ.site — Comparison Catalog

Article website with comparisons of plugins, web services, and AI tools.
Fully custom build (no CMS/engines), dynamic site + custom admin panel.
This file is the single source of truth for the project's architecture and rules; every architectural decision is recorded here.
Note: the Laravel skeleton ships its own `AGENTS.md` / `CLAUDE.md` boilerplate — this file must be kept as the authoritative one.

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+ (local 8.5 via Kettle), Laravel 13 (laravel/framework ^13.17) |
| Public frontend | Blade + Livewire 3 + Alpine, Tailwind CSS |
| Admin panel | Filament 5.8 at `/admin` |
| Content i18n | spatie/laravel-translatable (JSONB columns) |
| Frontend build | Vite + npm |
| Database | PostgreSQL 16+ (full-text search, JSONB) |
| Cache/queues (prod) | Redis + Horizon; locally the `database` driver |
| Quality | Pest, Laravel Pint, Larastan (PHPStan) |
| Production (later) | VPS + Docker Compose: Caddy, php-fpm, Postgres, Redis, Horizon worker, scheduler |

## Languages & i18n (designed in from day one)

- **Site language is English at launch**, but the architecture must support adding locales later without schema changes.
- **UI strings** always go through Laravel lang files (`lang/en/*.php`) — no hardcoded copy in views/components.
- **Content translations**: translatable columns (title, excerpt, body_html, description, verdicts, SEO fields) are stored as JSONB via `spatie/laravel-translatable`; Filament forms use per-locale tabs.
- **Per-locale slugs**: slug is a translatable attribute; routing resolves slugs per requested locale.
- **URLs**: the default locale is served from the root (`/articles/{slug}`); additional locales get a prefix (`/ru/articles/{slug}`). The URL is the source of truth for locale — no session-based language switching on public pages.
- **hreflang**: every public page emits `<link rel="alternate" hreflang>` for all published locales plus `x-default`; canonical is per-locale.
- **Search**: FTS `tsvector` is built from the default locale at launch; per-locale indexes are added when new locales ship.
- **Fallback**: untranslated fields fall back to the default locale (Spatie fallback behavior).
- Sitemap/RSS: default locale at launch, extended per locale later.

## Local environment (no Docker)

- Site: `php artisan serve` → http://127.0.0.1:8000; admin at `/admin`
- Vite: `npm run dev` (separate process)
- Redis is NOT used locally: `.env` has `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `SESSION_DRIVER=database`
- Page checks: browser + agent (Kilo Code)
- Migration to hosting happens near the end of the project, via Docker Compose

### Upload limits (videos up to 300MB)

`php artisan serve` spawns a child PHP process that **drops `-d` ini flags**, so video uploads (>2M PHP default) fail. For local media uploads run the built-in server directly so the limits apply to the serving process:

```bash
cd public
~/.kettle/bin/php -d upload_max_filesize=300M -d post_max_size=305M -d max_execution_time=300 \
  -S 127.0.0.1:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
```

- Livewire temp upload limit is in `config/livewire.php` → `temporary_file_upload.rules` (`max:307200` = 300MB); raise together with the PHP flags.
- Filament `FileUpload::maxSize()` in `app/Filament/Pages/MediaLibrary.php` is 307200 KB (300MB).

### Media manager (filesystem-based file manager)

The admin media manager (`/admin/media-library`, nav label "File manager") works directly on the `uploads/` directory of the public disk — no spatie/media DB records involved for new uploads (legacy spatie `MediaItem` records and `/storage/<id>/...` URLs from the first iteration remain valid in articles):

- Folder navigation: breadcrumbs + folder cards, "New folder" button, uploads go into the **current folder** (`dir` param), file/folder delete (folders only when empty).
- `MediaApiController` — `GET /admin/media/api?dir=uploads/x` (folder listing: `{dir, folders[], files[]}`; `?all=1` returns a flat recursive list for the picker), `POST /admin/media/api/upload` (multipart `file` + `dir`, 300MB, uniquifies names `name-1.ext`), `POST /admin/media/api/folder` (create), `POST /admin/media/api/delete` (file, or folder only when empty). Auth-protected, `..` rejected, paths clamped under `uploads/`.
- The page is fully Alpine-driven (`fileManager()` in a script block, `wire:ignore` container): breadcrumb navigation, compact upload tray (84px chips with inline progress bars — not full-width), type filter tabs, search within folder, lightbox with ←/→/Esc, copy URL/path.
- `App\Filament\Forms\Components\MediaPickerField` fetches `?all=1` (flat list) and stores the **relative** storage path (`/storage/uploads/...`); its own upload zone posts to the same upload endpoint.
- Video thumbnails are browser first-frames (`<video preload="metadata" src="…#t=0.1">`); images render as-is with lazy loading.
- **Do not use Filament `FileUpload` on this page** — on a custom page its state cast runs before the submit action and drops `TemporaryUploadedFile` instances (the cause of the "says uploaded but nothing appears" bug). Uploads go through XHR to the API.
- **`addMedia($path)` DELETES the source file by default** (spatie FileAdder unlinks it unless `->preservingOriginal()` is chained). Always chain `preservingOriginal()` when the source lives on the public disk (imports, anything referenced by `body_html`); temp uploads from livewire-tmp may omit it. This deletion is what once wiped the whole `uploads/` tree — restored via git (uploads are git-tracked).

### Environment status (macOS, checked 2026-09-11)

| Tool | Status |
|---|---|
| PHP 8.5.8 (Kettle runtime, `~/.kettle/bin/php`) | OK; PDO drivers available: pgsql, mysql, sqlite |
| Composer 2.10.3 | OK |
| Node v24 / npm 11 | OK |
| PostgreSQL 17.11 (Homebrew) | running on port 5432; DB `claz` created |
| Redis | not needed locally |
| Local admin login | admin@claz.site (password set at provisioning; reset via `php artisan tinker --execute="App\Models\User::where('email','admin@claz.site')->first()->update(['password'=>'NEW_PASSWORD']);"`) |
| Test DB | `claz_testing` (phpunit.xml points tests at Postgres; sqlite is not used because of PG-specific indexes) |

## Progress

- [x] Milestone 1 — skeleton: git repo, Laravel project, Filament 5.8 panel, admin user, local Postgres
- [x] Milestone 2 — DB schema: 14 migrations (categories, tools, criteria, tool_criteria, articles, tags, article_tag, comparisons + items + scores, tool_links, click_events, vs_pages, banners, comments, settings), domain enums in `app/Enums`, CriteriaSeeder (7 default criteria)
- [x] Milestone 3 — Filament CRUD: Tools, Criteria, Categories, Tags (per-locale tabs, slug auto-generation via `App\Support\Slugger`, criteria values relation manager on Tools)
- [x] Milestone 4 — Articles: TipTap editor (Filament RichEditor), covers (public disk), draft/publish actions, reading time via `App\Support\ReadingTime`, models `Article`/`Comparison`/`Comment`
- [x] Milestone 5 — Comparisons: nested repeaters on the article edit page (blocks → tools), sortable (`sort_order`/`position`), snapshot sync via `App\Actions\SyncComparisonScores` ("Sync scores" header action copies live `tool_criteria` values into `comparison_scores` and removes stale rows)
- [x] Milestone 6 — Public site: home / category / article / tool pages (Blade + Tailwind v4), Livewire tools catalog with type filter and search, comparison table rendered from snapshots via `App\Services\ArticleRenderer` (`[[comparison:id]]` placeholders, unreferenced blocks appended), SEO: canonical/OG/Twitter/hreflang (per `config/app.php` `locales`), JSON-LD (WebSite, Article, ItemList, Product), route-model binding by per-locale slug
- [x] Milestone 7 — VS pages + `/go/{code}` tracking (public comparison-table links already point to `/go/{code}`)
- [x] Milestone 8 — Comments with pre-moderation (guest form on Livewire + honeypot + rate limit + HTMLPurifier, `CommentsResource` with approve/reject bulk actions, `SiteStats` dashboard widget) + banners (`Banner` model with placement/date/active scoping, `<x-banner>` component wired into layout header / article sidebar / article body via `ArticleRenderer`, `BannerResource`)
- [x] Milestone 9 — Polish: sitemap.xml + rss.xml (cached 1h, VS pairs for all published tools), robots.txt, Horizon (dashboard gated via `viewHorizon`), GitHub Actions CI (Pint + PHPStan + Pest on Postgres 17 service), Docker Compose stack (Caddy + php-fpm + Postgres + Redis + Horizon + scheduler), deployment runbook below

## Architecture

### Layers

- `routes/web.php` — public routes, thin controllers
- `app/Livewire/*` — interactive public-site components (forms, filters, comments)
- `app/Filament/*` — admin panel (Resources, Widgets, Relation Managers)
- `app/Actions/*`, `app/Services/*` — domain logic (publishing, article rendering, tracking, SEO)
- `app/Models/*` + `app/Enums/*` — Eloquent models and enum classes

### Routes

`/`, `/category/{slug}`, `/articles/{slug}`, `/tools`, `/tools/{slug}`, `/vs/{a}-vs-{b}`, `/go/{code}` (affiliate redirect), `sitemap.xml`, `rss.xml`; admin at `/admin`. Non-default locales live under an `/{locale}/` prefix.

### Data model (core)

Columns marked `*` are translatable JSONB columns (spatie/laravel-translatable).

- `tools` — single tool entity: `type` enum(plugin/service/ai), name*, slug*, vendor, description*, logo, rating_avg, status(draft/published), published_at, soft deletes
- `criteria` — name*, description*, `kind` enum(score/bool/text), weight, sort_order, is_active; `tool_criteria` — live tool values (unique per tool+criteria)
- `articles` — title*, slug*, category, excerpt*, `body_html`* (TipTap WYSIWYG with comparison-block placeholders), cover, status, published_at, reading_time, SEO fields* (meta_title, meta_description), soft deletes
- `tags` + `article_tag` — name*, slug*
- `comparisons` (belongs to article) → `comparison_items` (tool_id, position, score, verdict*) → `comparison_scores` (criteria values snapshot) — **snapshot of values at publish time**
- `tool_links` — code for `/go/{code}` (unique), url, anchor*, is_affiliate; `click_events` — click tracking (tool_link_id, referer, ip_hash, created_at)
- `vs_pages` — auto VS pages `/vs/{a}-vs-{b}` built from tool data + optional editorial text* (intro, conclusion), is_published
- `banners` — placement enum(header/sidebar/in_article), image/html, url, dates, is_active
- `comments` — guest comments: article_id, parent_id (reserved threading), name, email, body, status(pending/approved/rejected), ip_hash
- `settings` — key-value config

### Key decisions

1. Published comparison tables use the snapshot (`comparison_scores`) and do not change when a tool card is edited; the admin panel has a "sync with live data" action
2. All affiliate transitions go through `/go/{code}` (302) + clicks recorded via the queue
3. Guest comments: HTMLPurifier, honeypot, rate limit, pre-moderation
4. SEO-first: canonical, OpenGraph (incl. `article:*` tags, og:locale), JSON-LD (WebSite+SearchAction, Organization, Article with wordCount/section/keywords, ItemList for comparison tables, FAQPage auto-extracted from the `#faq` ex-card section, BreadcrumbList via `<x-breadcrumbs>`, CollectionPage+ItemList on /tools and categories), hreflang (per-locale URLs, not per-page bug), sitemap, RSS with full `content:encoded` bodies; `/llms.txt` + `/llms-full.txt` for AI/LLM crawlers (dynamic routes, cached); robots.txt explicitly allows AI crawlers; canonical strips `?search`/`?type` noise but keeps `?page`; category pages 2+ are `noindex,follow`; Tool logos feed Product.image + og:image (`logo_url` accessor, MediaPickerField in ToolResource). Full-page cache on prod (Spatie responsecache) invalidated from Filament
5. Images: local disk `storage/app/public/uploads`, webp resize (Intervention) via queue; S3-compatible storage later via the Storage API
6. **URLs are root-relative everywhere** (site must survive host moves local↔prod): media paths stored in DB as storage-relative (`uploads/...`) or root-relative (`/storage/...`); the file manager's "Copy URL" copies `/storage/...`; `MediaPickerController` returns `/storage/...` in `url`/`rel`; accessors `cover_url`/`logo_url`/`image_url` emit `/storage/...`; Vite build assets are made root-relative via `Vite::createAssetPathsUsing()` in `AppServiceProvider::register()`. Absolute `url()` is allowed **only** for SEO artifacts that must be host-qualified (canonical, hreflang, OG/Twitter tags, JSON-LD, sitemap.xml, rss.xml). Never paste `http://127.0.0.1:8000/...` into article content — if imported content contains it, strip to root-relative before publishing
7. Roles: single admin-owner; the `role` column on users reserves room for future authors/editors
8. i18n as described in "Languages & i18n": English default, JSONB translatable columns, locale-prefixed URLs for non-default locales
9. Per-locale slug uniqueness is enforced with expression unique indexes `((slug->>'en'))` + GIN (jsonb_path_ops) on `slug` columns; add one expression index per new locale when it ships
10. Laravel pluralizes `criteria` as `criterias` — always pass the table name explicitly: `constrained('criteria')`; the same for relations: `belongsTo(Criterion::class, 'criteria_id')` (Laravel would derive `criterion_id`)
11. Tools and articles use RESTRICT foreign keys from `comparison_items` / `vs_pages` so referenced tools cannot be deleted accidentally
12. Never pass pre-encoded JSON strings to translatable attributes — always pass arrays (spatie double-encodes strings, which breaks per-locale slug lookups); seeder lookups use `where('slug->en', ...)` instead of raw JSON matches

## Code rules

- Style: Laravel Pint (default), PHPStan/Larastan level 6 — before committing run `./vendor/bin/pint && ./vendor/bin/phpstan`; `phpstan.neon` must keep `parseModelCastsMethod: true` (otherwise Larastan does not resolve `casts()` enum casts)
- No code comments except complex places
- DB enum values are PHP enum classes + casts; no magic strings
- Validation lives in FormRequests; logic lives in Actions/Services — no fat models
- Migrations are atomic with meaningful names; `migrate:fresh --seed` is local-only
- Slugs use a custom slug helper that handles non-Latin scripts when locales are added (Str::slug is not enough for non-English locales)
- When overriding `Filament\Resources\Resource` properties, repeat the parent's type with fully-qualified names (`\UnitEnum|string|null`, `\BackedEnum|string|null`) — unqualified names in the child namespace fail class compilation on PHP 8.5
- **Tailwind in Filament custom views**: Filament 5 bundles its own Tailwind CSS (purged from its own templates). Custom Blade views under `resources/views/filament/` do NOT get Tailwind utility classes compiled. Use raw CSS in `<style>` tags or inline styles for custom layouts in admin panel views.
- Secrets only in `.env` (never committed); keep `.env.example` up to date
- Git: `main` + feature branches, Conventional Commits (feat/fix/chore/docs)
- Pest tests for key flows: article publishing, VS page, `/go` redirect, comment pre-moderation
- Documentation (this file, commits, PR descriptions) in English; site content is English at launch

## Milestones

1. [x] Skeleton: git init, Laravel project, Filament, admin login, local DB
2. [x] DB schema: migrations for all tables + criteria seeds
3. [x] Filament CRUD: Tools, Criteria, Categories, Tags
4. [x] Articles: TipTap editor, covers, draft/publish
5. [x] Comparisons: comparison tables in articles (snapshot, sortable items)
6. [x] Public site: home / category / article / tool pages + SEO markup
7. [x] VS pages + `/go/{code}` tracking
8. [x] Comments with pre-moderation + banners
9. [x] Polish: sitemap/RSS, tests, CI, move to VPS (Docker Compose)

## Deployment runbook (VPS, Docker Compose) — DEPLOYED 2026-09-13 at pv-vps (217.177.72.205)

The server already runs nginx on 80/443 for other sites (pv-reviews.site, aquascape.club), so **nginx terminates TLS for claz.site and reverse-proxies to Caddy**:

1. Provision a VPS with Docker + Compose plugin; point DNS A/AAAA records at it
2. `git clone https://github.com/Lebedgor/claz.site.git && cd claz.site`
3. `cp .env.example .env`; set: `APP_KEY` (`openssl rand -base64 32`), `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://claz.site`, `DB_HOST=postgres`, `DB_USERNAME`/`DB_PASSWORD`, `REDIS_HOST=redis`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`
4. `docker compose up -d --build` — Caddy listens on `127.0.0.1:8080` (plain HTTP, no host 80/443), entrypoint runs migrations + caches config/views and copies **all of `public/`** (index.php, build/, robots.txt, css/) to the shared `/srv/public` volume — required so `php_fastcgi` resolves `index.php`
5. nginx vhost `/etc/nginx/sites-available/claz.site` (symlinked into `sites-enabled`): proxy_pass `http://127.0.0.1:8080` with `Host`, `X-Forwarded-For/Proto`, `client_max_body_size 300M`, `proxy_read_timeout 300s`; TLS via certbot (`certbot --nginx -d claz.site -d www.claz.site --redirect`), auto-renewal already scheduled
6. Create/reset the admin user: `docker compose exec app php artisan tinker --execute="App\Models\User::where('email','admin@claz.site')->first()->update(['password' => '...']);"` — on import the local admin record comes along, so reset the password instead of creating a duplicate
7. Content import: `pg_dump -Fc` locally → `docker compose cp dump postgres:/tmp/` → `pg_restore` (drop schema public first). The uploads volume shadows the image copy, but git-tracked article images are **auto-seeded into the volume on every container start**: the Dockerfile copies `storage/app/public` to `/opt/uploads-seed` and the entrypoint runs `rsync -a --ignore-existing /opt/uploads-seed/ /var/www/html/storage/app/public/` (busybox `cp -n` silently skipped new directories — do not replace rsync with cp). Only the initial DB import is manual now.
8. Updates: `./scripts/deploy.sh [SeederClass,SeederClass...]` — git push + `git pull && docker compose up -d --build` on the VPS (migrations run on app start), optional article seeders, cache clear, and a health check. Without arguments it just ships code+images. `docker compose logs -f app caddy horizon` to watch; `docker compose ps caddy` shows `127.0.0.1:8080->80`

CI (GitHub Actions) is provided but **disabled**: the workflow lives at `.github/workflows/ci.yml.disabled` (Pint + PHPStan level 6 + Pest on PHP 8.4 against a Postgres 17 service). Quality gates run locally before commits (Pint + PHPStan + Pest). To re-enable CI, rename the file back to `ci.yml`.

# Long-form Article Design System

All long-form articles, comparisons, reviews, and deep reviews must use the shared `.ex-article` design system.

The design system defines the **visual language and reusable components**, but it does NOT define a fixed page layout.

The goal is:

> **Consistent brand identity + significant visual variety between articles.**

Every article should feel like it belongs to the same website while having its own visual composition.

---

## 1. Core Principle: Design System, Not Template

Do NOT reproduce the exact structure or visual composition of previous articles.

The following must NOT become a fixed pattern:

- identical section order
- identical card sequence
- identical color sequence
- identical number of columns
- identical image arrangement
- identical pricing block placement
- identical summary layout
- identical CTA layout
- identical spacing rhythm
- identical verdict card design

Reusable components are encouraged.

Reusable **page compositions** are not.

Before writing the HTML, determine which visual structure best fits the article's subject and content.

---

# 2. Visual Archetypes

Choose one primary visual archetype for each article.

The choice should depend on the subject rather than being selected randomly.

### 🏆 Winner-first

Best for buyer-intent comparisons.

Typical flow:

Lead → key winner → reasons → competitors → pricing → comparison → use cases → verdict

### ⚖️ Head-to-head

Best for A vs B comparisons.

Typical flow:

Lead → quick comparison → Product A → Product B → direct comparison → pricing → who should choose each → verdict

### 🔬 Deep Review

Best for detailed reviews.

Typical flow:

Lead → methodology → UX → features → performance → pricing → screenshots → limitations → verdict

### 📊 Data-first

Best for AI models, SaaS, pricing, benchmarks, analytics, and quantitative comparisons.

Typical flow:

Lead → key numbers → charts → benchmark/data analysis → qualitative analysis → pricing → verdict

### 🎯 Use-case-first

Best when different products are better for different users.

Typical flow:

Lead → best for X → best for Y → best for Z → comparison → pricing → overall conclusion

### 🧩 Feature-first

Best for plugin/app comparisons.

Typical flow:

Lead → major feature → second feature → third feature → pricing → UX → comparison → verdict

### 📰 Editorial

Best for magazine-style or visually rich content.

Use larger images, pull quotes, asymmetric layouts, fewer but larger content blocks, and more whitespace.

### 📑 Research

Best for analytical articles.

Use methodology, structured data, charts, tables, scoring, evidence, limitations, and conclusions.

---

# 3. Do Not Use a Fixed Section Order

There is no mandatory sequence such as:

Lead → nav → verdict → images → pricing → grids → summary → ROI → CTA.

Instead, select the components that genuinely support the article.

A long-form article should normally contain approximately 5–9 major visual component types, depending on its length.

Do NOT force unused components into the article merely because they exist in the design system.

---

# 4. Shared Brand Language

The following should remain consistent across the website:

- `.ex-article` scoped styling
- typography
- overall content width
- spacing scale
- border-radius language
- shadow language
- button styling
- general visual hierarchy
- accent color family
- navigation styling
- image treatment
- overall editorial quality

Consistency should come from these shared rules, not from repeating the same page structure.

---

# 5. Controlled Visual Variation

Each new article must intentionally vary several of the following:

- section order
- dominant component type
- card layout
- image layout
- number of columns
- content density
- chart placement
- comparison format
- accent color usage
- heading scale
- amount of whitespace
- CTA presentation
- verdict presentation
- pricing presentation

At least 4–6 of these dimensions should differ from the dominant layout used in recent articles.

Do not make every article visually experimental. Variation should remain professional and coherent.

---

# 6. Component Library

The following components are available but OPTIONAL.

Use only components that improve the article.

### Lead

A strong opening paragraph, normally 17px, with important terms emphasized.

### Navigation

`.ex-nav-row` / `.ex-nav-btn`

Navigation may be used when the article is long enough to benefit from section navigation.

It does not have to be visually identical in every article.

### Section

Emoji-prefixed `h2` headings with meaningful `id` anchors.

Use `scroll-margin-top: 110px` for anchored headings.

Use horizontal dividers selectively rather than mechanically between every section.

### Verdict / Recommendation

Verdict cards can use:

- compact card
- large winner card
- horizontal recommendation
- scorecard
- bordered callout
- dark card
- gradient card
- editorial callout

Do not always use the same verdict design.

### Image Presentation

Images may be displayed as:

- single hero screenshot
- 2-column comparison
- 3-column gallery
- image + explanation
- image + metrics
- before/after comparison
- featured image with supporting thumbnails
- screenshot gallery
- large screenshot followed by compact observations

Choose the presentation according to the content.

### Pricing

Pricing can be represented as:

- `.ex-card-bordered`
- pricing comparison table
- compact pricing cards
- horizontal price tiers
- cost-per-scale analysis
- pricing + recommendation
- SVG cost chart

Do not place a pricing card in the same location in every article.

### Comparison

Possible formats:

- standard table
- feature matrix
- scorecard
- ranking
- horizontal comparison cards
- category-by-category comparison
- visual bar chart
- pros/cons comparison

Choose the format that communicates the information most efficiently.

### Scenario Cards

Use `.ex-grid` when multiple scenarios, user types, or use cases need separate treatment.

Do not automatically use a 3-column grid.

Possible layouts:

- 2 columns
- 3 columns
- 4 compact cards
- horizontal cards
- featured scenario + smaller alternatives

### Data Visualization

Use inline SVG charts when quantitative information benefits from visualization.

Possible chart types:

- horizontal bars
- stacked bars
- ranking bars
- price/scale comparison
- score comparison
- feature availability
- timeline
- cost progression

Use `"custom — talk to sales"` where pricing is negotiated.

Clearly label estimates and include the relevant data date.

### Summary

`.ex-summary-bg` may be used before the final verdict.

However, summaries can also use:

- compact checklist
- scorecard
- winner matrix
- comparison table
- editorial conclusion

Do not always use the same summary component.

### ROI / Value Banner

The gradient ROI-style banner is optional.

Use it when the article has a meaningful cost/value story.

Do not include it simply because it exists in the component library.

### Final CTA

The final CTA may use:

- dark CTA block
- strong-border verdict
- compact action panel
- winner + CTA combination
- editorial conclusion

Do not use the same CTA composition in every article.

---

# 7. Color System

Use the established accent palette, but do NOT assign colors mechanically.

Existing semantic colors may include:

- cyan
- green
- orange
- violet
- rose

Green can communicate a positive recommendation.

Orange/rose can communicate caution or limitations.

However, the same semantic meaning may be expressed through different visual treatments.

Do not use all accent colors in every article.

A professional article may intentionally use:

- one dominant accent
- two accents
- mostly neutral colors + one accent
- several accents when comparison categories genuinely require them

Avoid predictable sequences such as:

cyan → green → orange → violet → rose.

---

# 8. Emoji Rules

Use thematic emoji in section headings and major card titles where appropriate.

Examples:

- 📸 visual/media features
- 📊 analytics/data
- 💰 pricing
- ⚡ performance
- 🔍 SEO
- 🎯 use cases
- ⚠️ warnings
- 🏆 winners
- ⭐ ratings
- 🔬 methodology
- 🧩 features

Do not use emoji merely for decoration.

The emoji should help communicate the topic of the section.

---

# 9. Ratings

When reliable vendor/App Store ratings are available, they may be displayed as compact chips.

Example:

`★ 4.9 · 9,596 App Store reviews`

Ratings should appear naturally where they support the evaluation.

Do not mechanically repeat rating chips throughout the article.

---

# 10. Screenshots and Images

Use real UI screenshots from official sources whenever possible.

Preferred sources include:

- vendor websites
- official documentation
- official product pages
- Shopify App Store CDN

Shopify App Store screenshots may use URLs from:

`cdn.shopify.com/app-store/listing_images/...`

Screenshots must be downloaded locally to:

`storage/app/public/uploads/article/`

Do not hotlink external screenshots.

Every screenshot must have a useful figure caption.

Example:

`Screenshot: Judge.me`

Use `figure.ex-fig` for standalone figures where appropriate.

Images should support the argument rather than merely fill empty space.

---

# 11. Content Density and Visual Rhythm

Avoid long sequences of visually identical cards.

Bad pattern:

Card → Card → Card → Card → Card

Prefer varied visual rhythm:

Large visual → short explanation → metrics → comparison → quote → chart → compact cards

or:

Winner → screenshot → feature analysis → table → pricing → scenario cards

Mix:

- large and small components
- dense and spacious sections
- images and text
- tables and cards
- charts and prose

The reader should feel a change of visual rhythm as they move through the article.

---

# 12. Avoid Component Overuse

Do not:

- create a card for every paragraph
- put every statistic inside a card
- use grids when normal prose is clearer
- use a chart when a sentence is sufficient
- use decorative gradients without informational purpose
- repeat the same callout several times
- turn the entire article into a collection of boxes

Cards should communicate hierarchy, comparison, or important information.

---

# 13. Article-Specific Visual Identity

Each article should have a visual "center of gravity".

Examples:

### Product comparison

Product screenshots + comparison matrix

### Pricing comparison

Charts + pricing blocks + cost analysis

### AI comparison

Benchmarks + scores + model cards

### SEO comparison

Search/SEO diagrams + feature matrix + scenario cards

### Plugin review

UI screenshots + feature analysis + UX observations

### Best-of list

Ranking + winner cards + use-case cards

The dominant visual component should reflect the article's subject.

---

# 14. Responsive Layout

All layouts must remain responsive.

Use:

- 1 column on mobile
- 2 columns where appropriate on tablet
- 2–4 columns on desktop depending on content

Do not force desktop grids when the content becomes difficult to read.

Large screenshots, tables, and charts must remain usable on narrow screens.

---

# 15. Accessibility and Usability

Maintain:

- sufficient text contrast
- readable font sizes
- clear heading hierarchy
- descriptive image captions
- meaningful link text
- visible interactive states
- mobile-friendly spacing

Visual variety must never reduce usability.

---

# 16. SEO and Semantic Structure

Use semantic HTML.

Every major section should have a meaningful heading and anchor when appropriate.

Do not manually add JSON-LD.

JSON-LD for:

- Article
- ItemList
- Review
- AggregateRating

is generated automatically by the controllers.

The article HTML should therefore focus on content, structure, and presentation.

---

# 17. Article Length

Target:

**25,000–30,000 characters without spaces**

The target applies to the body text and should not be reached by adding meaningless filler.

Long-form content should prioritize:

- useful comparisons
- concrete evidence
- screenshots
- pricing
- limitations
- practical scenarios
- clear recommendations

---

# 18. Final Quality Rule

Before finalizing an article, mentally compare its composition with the previous long-form articles.

If the page follows the same pattern:

`Lead → pills → identical cards → screenshots → pricing card → grid → summary → gradient → dark CTA`

then redesign the composition.

The article should still clearly belong to the same design system, but a reader should not be able to predict the next visual block simply because they have read another article on the site.

**Shared design language. Different editorial composition.**

# 19. Article Discussion Comments

Long-form comparison and review articles should include **2–5 useful discussion comments** after the main article content.

The purpose of comments is to extend the article with additional questions, edge cases, practical scenarios, and perspectives that are relevant to the topic.

Comments must NOT exist merely to increase page text or create artificial SEO content.

## Comment Quality

Each generated comment should add information that is:

- relevant to the article
- specific to the product/topic
- useful to another reader
- naturally phrased
- different from the main article
- capable of representing a realistic reader question or observation

Avoid generic comments such as:

- "Great article!"
- "Very useful comparison."
- "Thanks for sharing."
- "This helped me a lot."
- "I agree with this."

These provide no meaningful value.

## Comment Types

Generate a varied combination of comment types.

Possible types include:

### ❓ Practical Question

A realistic question about implementation, pricing, compatibility, migration, limits, or setup.

### 🔍 Edge Case

A less obvious situation that may affect the recommendation.

### ⚖️ Alternative Comparison

A question comparing two products in a specific use case not fully covered in the article.

### 💰 Cost Question

A question about pricing at a particular store size, traffic level, order volume, or review volume.

### 🧑‍💻 Technical Question

A question about integrations, performance, customization, API, SEO, themes, compatibility, or technical limitations.

### 🎯 Use-case Question

A question from a specific type of business or user.

### 💡 Additional Insight

A short observation that adds useful context rather than simply praising the article.

## Comment Distribution

Normally generate **2–5 comments** depending on article complexity.

Suggested distribution:

- short article: 2–3 comments
- standard long-form article: 3–4 comments
- highly detailed comparison: 4–5 comments

Do not force 5 comments when only 2–3 genuinely useful discussion points exist.

## Comment Independence

Comments should introduce information or questions that are not simply repetitions of existing paragraphs.

Avoid copying sentences, statistics, conclusions, or wording from the article.

Comments can reference the article naturally, but should extend the discussion.

## Natural Reader Profiles

Where appropriate, vary the implied reader perspective:

- small store owner
- growing store owner
- developer
- agency
- SEO-focused user
- budget-conscious user
- high-volume store
- technical user

Do not label comments with artificial personas unless the existing comment UI requires it.

## SEO Rule

Comments should naturally contain relevant terminology and long-tail concepts when appropriate.

Do NOT keyword-stuff comments.

Do NOT deliberately repeat the primary keyword.

The comment should read naturally even if search engines did not exist.

The SEO value comes from useful additional topical information, not from artificially increasing keyword frequency.

## Replies

A comment may optionally receive a short editorial/site reply when the answer adds meaningful information.

Do not automatically create replies for every comment.

A typical article should contain:

- 2–5 reader comments
- 0–3 editorial replies

Replies should be concise and should not simply repeat the article.

## Factual Accuracy

Do not invent user experiences, purchases, test results, or claims presented as real customer experiences.

Comments represent realistic hypothetical discussion unless the comment system explicitly marks them as editorial/generated content.

Never fabricate statements such as:

> "I have been using this app for three years and..."

unless such a real user statement exists in the source material.

## Placement

Comments should appear after the main article content and before the final site-level navigation/footer area, using the existing article/comment UI.

They should feel like a natural continuation of the article rather than another SEO block.

## Diversity

Comments should vary in:

- length
- question structure
- vocabulary
- perspective
- subject
- level of technical detail

Do not generate five comments that all ask essentially the same question.

## Final Check

Before publishing, verify:

1. Every comment adds useful information or raises a meaningful question.
2. No comment exists solely for SEO.
3. No fake personal experience is presented as fact.
4. No comment repeats the article verbatim.
5. Comments contain natural topic terminology.
6. The discussion feels plausible rather than artificially manufactured.

**Editor decision (updated 2026-09-12):** article `body_html` is edited via a per-article `editor_mode` toggle (column + `EditorMode` enum) with **two editors on SEPARATE state paths**:
- `tiptap` (default for new articles) — Filament `RichEditor::make('body_tiptap')` with a curated toolbar (tables, code blocks, links, images). Round-trip **normalizes markup** — good for simple visual writing.
- `html` — Filament `CodeEditor::make('body_html_src')` (HTML source) for design-heavy `ex-article` markup.
- Both are hydrated from the record via `afterStateHydrated` (`$component->state($record->getTranslation('body_html', 'en'))`); the Radio `afterStateUpdated` hook transfers content between the paths on mode switches (TipTap JSON → HTML through the RichEditor's own `getTipTapEditor()`); `mutateFormDataBeforeSave` on both pages picks the body by mode, writes `body_html.en`, computes `reading_time`, and unsets the temp paths.
- **Never bind two editors to the same state path**: the hidden RichEditor's StateCast converts the shared state to TipTap JSON (`{type, content}`) on hydration — the CodeEditor then shows nothing and `ReadingTime::estimate()` dies with "array given".
- **Warning:** opening a design-heavy article in TipTap mode normalizes the custom `ex-article` markup on save — legacy `ex-*` articles are backfilled to `html` mode; keep them there. Third-party WYSIWYGs are still incompatible with Filament 5 — do not add them.
- Regression tests: `tests/Feature/ArticleEditorModesTest.php` (hydration, mode-switch transfer, TipTap save).

**Technical pitfalls (check before saving):**
- Balanced inline tags in `body_html`: every `<b>` needs `</b>` (not `</strong>` — mismatched pairs make all following text bold)
- No stray `HTML;`-like lines inside nowdoc blocks in seeders (terminates the heredoc early → ParseError)
- Image paths: `storage/app/public/uploads/article/**` → URL `/storage/uploads/article/...`; uploads are git-tracked (skeleton `.gitignore` files inside `storage/app` were removed deliberately)
- Comparison values are snapshots: after editing tools, use the article's "Sync scores" action in the admin panel
