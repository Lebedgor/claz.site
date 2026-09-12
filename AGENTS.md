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
4. SEO-first: canonical, OpenGraph, JSON-LD (Article, ItemList for comparison tables), hreflang, sitemap, RSS; full-page cache on prod (Spatie responsecache) invalidated from Filament
5. Images: local disk `storage/app/public/uploads`, webp resize (Intervention) via queue; S3-compatible storage later via the Storage API
6. Roles: single admin-owner; the `role` column on users reserves room for future authors/editors
7. i18n as described in "Languages & i18n": English default, JSONB translatable columns, locale-prefixed URLs for non-default locales
8. Per-locale slug uniqueness is enforced with expression unique indexes `((slug->>'en'))` + GIN (jsonb_path_ops) on `slug` columns; add one expression index per new locale when it ships
9. Laravel pluralizes `criteria` as `criterias` — always pass the table name explicitly: `constrained('criteria')`; the same for relations: `belongsTo(Criterion::class, 'criteria_id')` (Laravel would derive `criterion_id`)
10. Tools and articles use RESTRICT foreign keys from `comparison_items` / `vs_pages` so referenced tools cannot be deleted accidentally
11. Never pass pre-encoded JSON strings to translatable attributes — always pass arrays (spatie double-encodes strings, which breaks per-locale slug lookups); seeder lookups use `where('slug->en', ...)` instead of raw JSON matches

## Code rules

- Style: Laravel Pint (default), PHPStan/Larastan level 6 — before committing run `./vendor/bin/pint && ./vendor/bin/phpstan`; `phpstan.neon` must keep `parseModelCastsMethod: true` (otherwise Larastan does not resolve `casts()` enum casts)
- No code comments except complex places
- DB enum values are PHP enum classes + casts; no magic strings
- Validation lives in FormRequests; logic lives in Actions/Services — no fat models
- Migrations are atomic with meaningful names; `migrate:fresh --seed` is local-only
- Slugs use a custom slug helper that handles non-Latin scripts when locales are added (Str::slug is not enough for non-English locales)
- When overriding `Filament\Resources\Resource` properties, repeat the parent's type with fully-qualified names (`\UnitEnum|string|null`, `\BackedEnum|string|null`) — unqualified names in the child namespace fail class compilation on PHP 8.5
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

## Deployment runbook (VPS, Docker Compose)

1. Provision a VPS with Docker + Compose plugin; point DNS A/AAAA records at it
2. `git clone git@github.com:Lebedgor/claz.site.git && cd claz.site`
3. `cp .env.example .env`; set: `APP_KEY` (`openssl rand -base64 32`), `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<domain>`, `SITE_ADDRESS=<domain>`, `DB_HOST=postgres`, `DB_USERNAME`/`DB_PASSWORD`, `REDIS_HOST=redis`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis` (add to `.env`; compose passes env), regenerate admin password
4. Update `public/robots.txt` sitemap domain placeholder
5. `docker compose up -d --build` — entrypoint runs migrations + caches config/views and syncs build assets to the shared volume
6. Create the admin user: `docker compose exec app php artisan tinker --execute="App\Models\User::create(['name' => 'Admin', 'email' => '...', 'password' => '...']);"`
7. Updates: `git pull && docker compose up -d --build` (migrations run on app start); `docker compose logs -f app caddy horizon` to watch

CI (GitHub Actions) is provided but **disabled**: the workflow lives at `.github/workflows/ci.yml.disabled` (Pint + PHPStan level 6 + Pest on PHP 8.4 against a Postgres 17 service). Quality gates run locally before commits (Pint + PHPStan + Pest). To re-enable CI, rename the file back to `ci.yml`.

## Article formatting standard (long-form reviews)

All long-form articles (comparisons, deep reviews) follow the design system introduced in the Shopify review apps article (seeded by `ShopifyReviewsSeeder`). The look is scoped CSS inside `body_html` (`.ex-article` classes, modeled after extended-reviews.com) — independent from Tailwind compilation. Checklist:

**Structure (in order):**
1. Lead paragraph (17px, bold key terms)
2. Colored nav pills row (`.ex-nav-row` / `.ex-nav-btn` with emoji + title + gray sub-label) — anchors to section `id`s; `scroll-smooth` on `<html>`, `scroll-margin-top: 110px` on headings (sticky header offset)
3. Sections with emoji-prefixed `h2 id="anchor"` + `hr` divider between sections
4. Colored accent card with the verdict right under each reviewed entity's heading (cyan/green/orange/violet/rose, semantic: green = pick, orange/rose = warning)
5. Image grids (`.images-block`: 1 col mobile / 2 tablet / 3 desktop, hover lift) + standalone `figure.ex-fig`
6. "Pricing in practice" blocks — `.ex-card-bordered` (2px cyan) with `.ex-inner` panels and `.ex-dashed` key points
7. Card grids for scenario/multi-point content (`.ex-grid` with tinted bold titles)
8. Summary panel before the final verdict (`.ex-summary-bg` + white cards with 4px colored left bar + tinted chip)
9. Gradient ROI-style banner with glassmorphism tiles (135° indigo→violet, `rgba(255,255,255,0.15)` tiles)
10. Green strong-border verdict card + dark final CTA block (`#0f172a`)

**Editorial rules:**
- Emoji in every section heading and card title; thematic (📸 for visual tools, 📊 for analytics, ⚠️ for warnings)
- App Store / vendor ratings shown as chips (`★ 4.9 · 9,596 App Store reviews`) in verdict cards
- Real UI screenshots from official sources (vendor sites, Shopify App Store CDN `cdn.shopify.com/app-store/listing_images/...`) — downloaded to `storage/app/public/uploads/article/` (never hotlinked); source credited in each figcaption ("Screenshot: <vendor>")
- Cost/scale data visualized as inline SVG bar charts (with "custom — talk to sales" bars where pricing is negotiated); label estimates with the data date
- Target volume: 25–30k characters without spaces of body text
- JSON-LD (Article + ItemList + Review/AggregateRating) is generated automatically by the controllers — no manual markup

**Editor decision (fixed):** articles are edited with the Filament RichEditor (file attachments upload to `storage/app/public/uploads/editor/`, public disk) plus the "Media library" admin page (upload / preview / copy URL / delete). Third-party WYSIWYG replacements (awcodes/filament-tiptap-editor, CKEditor, TinyMCE) are incompatible with Filament 5 or would flatten the custom `ex-article` markup on re-save — do not swap editors for design-heavy articles; edit their HTML source via Media library Copy URL instead.

**Technical pitfalls (check before saving):**
- Balanced inline tags in `body_html`: every `<b>` needs `</b>` (not `</strong>` — mismatched pairs make all following text bold)
- No stray `HTML;`-like lines inside nowdoc blocks in seeders (terminates the heredoc early → ParseError)
- Image paths: `storage/app/public/uploads/article/**` → URL `/storage/uploads/article/...`; uploads are git-tracked (skeleton `.gitignore` files inside `storage/app` were removed deliberately)
- Comparison values are snapshots: after editing tools, use the article's "Sync scores" action in the admin panel
