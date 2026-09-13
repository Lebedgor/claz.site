<?php

namespace Database\Seeders;

use App\Actions\SyncComparisonScores;
use App\Enums\ArticleStatus;
use App\Enums\EditorMode;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Criterion;
use App\Models\Tag;
use App\Models\Tool;
use App\Models\ToolLink;
use App\Support\ReadingTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ElementorVsBricksSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::withTrashed()->where('slug->en', 'elementor-vs-bricks')->exists()) {
            $this->command->info('Article already exists — skipped to preserve admin edits. Delete the article to rebuild it from the seeder.');

            return;
        }

        $category = Category::query()->where('slug->en', 'wordpress-plugins')->first()
            ?? Category::create([
                'name' => ['en' => 'WordPress plugins'],
                'slug' => ['en' => 'wordpress-plugins'],
                'description' => ['en' => 'Honest reviews and comparisons of plugins and tools that make WordPress sites faster, prettier and more profitable.'],
                'sort_order' => 40,
            ]);

        foreach (['wordpress', 'page-builders', 'web-design'] as $tagSlug) {
            Tag::query()->where('slug->en', $tagSlug)->first()
                ?? Tag::create(['slug' => ['en' => $tagSlug], 'name' => ['en' => str_replace('-', ' ', $tagSlug)]]);
        }

        $tools = [];
        $criteria = [];
        foreach (Criterion::query()->orderBy('sort_order')->get() as $criterion) {
            $criteria[$criterion->getTranslation('name', 'en')] = $criterion;
        }

        $toolData = [
            'Elementor' => [
                'type' => ToolType::Plugin,
                'vendor' => 'Elementor',
                'rating' => 8.6,
                'description' => 'The most-installed WordPress page builder: 22M+ sites, a visual drag-and-drop editor with 85+ Pro widgets, Theme Builder, Form Builder, Popup Builder, WooCommerce Builder, AI content generation and a 2026 Editor V4 (Atomic) update that reduces nested divs and improves TTFB to 109ms on optimized hosting.',
                'affiliate' => false,
                'url' => 'https://elementor.com',
                'values' => ['Ease of use' => 9, 'Features' => 9, 'Performance' => 7, 'Pricing' => 7, 'Support & docs' => 8, 'Free plan' => true, 'Open source' => false],
            ],
            'Bricks Builder' => [
                'type' => ToolType::Plugin,
                'vendor' => 'Bricks',
                'rating' => 9.0,
                'description' => 'A performance-first visual WordPress theme builder: Vue.js editor, 110+ elements, Query Loop Builder, CSS Classes and Variables, native Gutenberg block rendering, WooCommerce Builder, Menu Builder and Popup Builder — all on a flat $79/year or $599 lifetime license with no per-site overage.',
                'affiliate' => false,
                'url' => 'https://bricksbuilder.io',
                'values' => ['Ease of use' => 7, 'Features' => 9, 'Performance' => 10, 'Pricing' => 9, 'Support & docs' => 7, 'Free plan' => false, 'Open source' => false],
            ],
        ];

        foreach ($toolData as $name => $data) {
            $tool = Tool::query()->where('slug->en', Str::slug($name))->first();

            if ($tool === null) {
                $tool = Tool::create([
                    'type' => $data['type'],
                    'status' => ToolStatus::Published->value,
                    'name' => ['en' => $name],
                    'slug' => ['en' => Str::slug($name)],
                    'vendor' => $data['vendor'],
                    'description' => ['en' => $data['description']],
                    'rating_avg' => $data['rating'],
                ]);
            }

            foreach ($data['values'] as $criterionName => $value) {
                $criterion = $criteria[$criterionName] ?? null;

                if ($criterion === null) {
                    continue;
                }

                $existing = $tool->criteria()->wherePivot('criteria_id', $criterion->getKey())->first();

                if ($existing === null) {
                    $tool->criteria()->attach($criterion->getKey(), ['value' => $value]);
                }
            }

            if ($tool->links()->count() === 0) {
                ToolLink::create([
                    'tool_id' => $tool->getKey(),
                    'code' => Str::slug($name),
                    'url' => $data['url'],
                    'anchor' => ['en' => 'Visit '.$name],
                    'is_affiliate' => $data['affiliate'],
                ]);
            }

            $tools[$name] = $tool;
        }

        $article = Article::create([
            'category_id' => $category->getKey(),
            'title' => ['en' => 'Elementor vs Bricks Builder (2026): The Honest Comparison for WordPress'],
            'slug' => ['en' => 'elementor-vs-bricks'],
            'excerpt' => ['en' => 'Elementor and Bricks Builder are two of the most debated WordPress page builders. We compared pricing, performance, ease of use, design flexibility, developer features and ecosystem maturity to find the right builder for every type of WordPress site.'],
            'body_html' => ['en' => self::ARTICLE_BODY],
            'cover' => 'uploads/article/apps/elementor/hero.jpg',
            'status' => ArticleStatus::Published->value,
            'published_at' => now(),
            'editor_mode' => EditorMode::Html->value,
            'meta_title' => ['en' => 'Elementor vs Bricks Builder: Which WordPress Builder Wins in 2026?'],
            'meta_description' => ['en' => 'Honest comparison of Elementor vs Bricks Builder: pricing, performance, features, ease of use and which WordPress page builder is right for your project.'],
        ]);

        $tagIds = Tag::query()->whereIn('slug->en', ['wordpress', 'page-builders', 'web-design'])->pluck('id');
        $article->tags()->sync($tagIds);

        $comparison = $article->comparisons()->create([
            'title' => ['en' => 'Elementor vs Bricks Builder: head-to-head'],
            'intro' => ['en' => 'Criteria scores from our testing methodology. Pricing, features and performance data are current as of September 2026.'],
            'verdict' => ['en' => 'Bricks wins on performance, pricing and developer control. Elementor wins on ease of use, ecosystem size and beginner accessibility. The right choice depends on your technical skill level and whether raw speed or rapid prototyping matters more.'],
            'sort_order' => 0,
        ]);

        $items = [
            ['Bricks Builder' => ['score' => 9.0, 'verdict' => 'Best performance and pricing — Vue.js editor, clean code output, $79/yr or $599 lifetime with no site limits']],
            ['Elementor' => ['score' => 8.6, 'verdict' => 'Best ecosystem and ease of use — 22M+ sites, 85+ Pro widgets, visual drag-and-drop with the lowest learning curve']],
        ];

        $position = 0;
        foreach ($items as $pair) {
            foreach ($pair as $name => $data) {
                $comparison->items()->create([
                    'tool_id' => $tools[$name]->getKey(),
                    'position' => $position++,
                    'score' => $data['score'],
                    'verdict' => ['en' => $data['verdict']],
                ]);
            }
        }

        app(SyncComparisonScores::class)->execute($article->refresh());

        $body = str_replace('COMPARISON_ID', (string) $comparison->getKey(), self::ARTICLE_BODY);
        $article->update([
            'body_html' => ['en' => $body],
            'reading_time' => ReadingTime::estimate($body),
        ]);
    }

    private const ARTICLE_BODY = <<<'HTML'
<p style="margin:0 0 18px; font-size:17px;"><b>Elementor</b> and <b>Bricks Builder</b> represent two fundamentally different philosophies in the WordPress page builder space. Elementor is the incumbent: 22 million+ active sites, a visual drag-and-drop interface that anyone can learn in an afternoon, and an ecosystem so large that almost every WordPress plugin offers an Elementor integration. Bricks is the challenger: a Vue.js-powered theme builder that outputs cleaner code, loads faster, costs less at scale and treats CSS classes as a first-class citizen rather than an afterthought. The debate between them is not about which is "better" in the abstract — it is about whether you value speed of learning or speed of rendering.</p>
<div class="ex-nav-row">
<a href="#philosophy" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🧩 <b>Philosophy</b><div class="ex-nav-sub">Plugin vs theme</div></div></a>
<a href="#pricing" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">💰 <b>Pricing</b><div class="ex-nav-sub">Flat vs tiered</div></div></a>
<a href="#performance" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#fff7ed; border:1px solid #fed7aa;">⚡ <b>Performance</b><div class="ex-nav-sub">Code output and speed</div></div></a>
<a href="#ease" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f5f3ff; border:1px solid #ddd6fe;">🎨 <b>Ease of use</b><div class="ex-nav-sub">Learning curve</div></div></a>
<a href="#features" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">⚙️ <b>Features</b><div class="ex-nav-sub">What each builder includes</div></div></a>
<a href="#dev" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🔧 <b>Developer features</b><div class="ex-nav-sub">CSS, dynamic data, hooks</div></div></a>
<a href="#scenarios" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">🧭 <b>Which to choose</b><div class="ex-nav-sub">Four scenarios</div></div></a>
<a href="#faq" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f5f3ff; border:1px solid #ddd6fe;">❓ <b>FAQ</b><div class="ex-nav-sub">Five honest answers</div></div></a>
</div>

<h2 id="philosophy">🧩 The fundamental difference: plugin vs theme</h2>
<p>Before comparing features, understand the architectural split. <b>Elementor is a plugin</b> — it installs alongside your existing WordPress theme and overrides the content area. You can deactivate Elementor and your theme remains. <b>Bricks is a theme</b> — it replaces your active theme entirely and controls everything from header to footer, archive templates to 404 pages. This is not a trivial distinction: it affects how you manage updates, how third-party plugins interact with your layouts, and what happens if you ever want to switch builders.</p>
<div class="ex-grid">
<div class="ex-card ex-card-cyan"><b>Elementor (plugin)</b><div style="margin-top:8px; font-size:14px; line-height:1.8;">
✅ Install alongside any theme<br>
✅ Deactivate without breaking your site<br>
✅ Works with Hello Elementor, Astra, GeneratePress, etc.<br>
⚠️ Output sits inside the theme's content wrapper<br>
⚠️ Two update streams (theme + plugin)
</div></div>
<div class="ex-card ex-card-green"><b>Bricks (theme)</b><div style="margin-top:8px; font-size:14px; line-height:1.8;">
✅ Full control from header to footer<br>
✅ Single update stream (theme only)<br>
✅ No theme/plugin conflict potential<br>
⚠️ Replaces your existing theme entirely<br>
⚠️ Cannot deactivate without losing all layouts
</div></div>
</div>
<div class="ex-dashed"><b>Why this matters:</b> if you manage client sites and need to swap builders mid-project, Elementor is safer — deactivate the plugin, activate a new theme, and the site structure survives. With Bricks, switching means rebuilding every template from scratch. Commitment is higher, but so is control.</div>

<h2 id="pricing">💰 Pricing: where Bricks wins decisively</h2>
<p>Elementor uses a tiered subscription model priced by the number of sites. Bricks uses a flat model with a lifetime option. At scale, the cost difference is dramatic.</p>

<div class="ex-grid">
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:10px;">Elementor pricing (2026)</div><div style="font-size:14px; line-height:1.8;">
<b>Free:</b> $0 — 32 core widgets, basic page building<br>
<b>Essential:</b> $59/yr — 1 site, 57 Pro widgets, Theme Builder<br>
<b>Advanced Solo:</b> $84/yr — 1 site, 85 Pro widgets, WooCommerce, Popup Builder<br>
<b>Advanced:</b> $99/yr — 3 sites, 85 Pro widgets<br>
<b>Expert:</b> $204/yr ($17/mo) — 25 sites, 5,000 Cloud Templates<br>
<b>One:</b> $180/yr ($15/mo) — 1 site, AI credits (25,000/mo), image optimization<br>
<b>One Agency:</b> $432/yr ($36/mo) — unlimited sites, 350,000 AI credits/mo
</div></div>
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:10px;">Bricks pricing (2026)</div><div style="font-size:14px; line-height:1.8;">
<b>Starter:</b> $79/yr — 1 site<br>
<b>Business:</b> $149/yr — 3 sites<br>
<b>Agency:</b> $249/yr — unlimited sites<br>
<b>Ultimate:</b> $599 one-time — unlimited sites, lifetime updates<br>
<b>No free tier</b> — all plans include every feature<br>
<b>No per-site feature gating</b> — Starter gets the same builder as Ultimate<br>
<b>60-day money-back guarantee</b>
</div></div>
</div>

<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">📊 Cost at different scales (annual):</div>
<svg viewBox="0 0 760 190" style="width:100%; height:auto;" role="img" aria-label="Annual cost comparison">
<text x="170" y="18" font-size="12" font-weight="700" fill="#334155">Sites</text>
<text x="310" y="18" font-size="12" font-weight="700" fill="#334155">Elementor</text>
<text x="520" y="18" font-size="12" font-weight="700" fill="#334155">Bricks</text>
<text x="170" y="46" font-size="12" text-anchor="end" fill="#475569">1 site</text>
<text x="310" y="46" font-size="12" font-weight="600" fill="#64748b">$59–$84/yr</text>
<text x="520" y="46" font-size="12" font-weight="600" fill="#10b981">$79/yr</text>
<text x="170" y="72" font-size="12" text-anchor="end" fill="#475569">3 sites</text>
<text x="310" y="72" font-size="12" font-weight="600" fill="#64748b">$99/yr</text>
<text x="520" y="72" font-size="12" font-weight="600" fill="#10b981">$149/yr</text>
<text x="170" y="98" font-size="12" text-anchor="end" fill="#475569">25 sites</text>
<text x="310" y="98" font-size="12" font-weight="600" fill="#f59e0b">$204/yr</text>
<text x="520" y="98" font-size="12" font-weight="600" fill="#10b981">$249/yr</text>
<text x="170" y="124" font-size="12" text-anchor="end" fill="#475569">Unlimited</text>
<text x="310" y="124" font-size="12" font-weight="600" fill="#ef4444">$432/yr</text>
<text x="520" y="124" font-size="12" font-weight="600" fill="#10b981">$249/yr or $599 lifetime</text>
<text x="170" y="158" font-size="11" fill="#64748b">Elementor pricing includes Pro plans as of September 2026. Bricks prices verified September 2026.</text>
<text x="170" y="178" font-size="11" fill="#64748b">Note: Elementor Essential ($59/yr) lacks WooCommerce and Popup Builder — the comparable feature set starts at Advanced Solo ($84/yr).</text>
</svg>
</div>

<div class="ex-card ex-card-neutral"><b>The pricing verdict:</b> for a single site, Elementor Essential ($59/yr) is cheaper than Bricks Starter ($79/yr) — but Elementor Essential lacks WooCommerce and Popup Builder, which are included in every Bricks plan. At the feature-equivalent tier (Elementor Advanced Solo at $84/yr vs Bricks Starter at $79/yr), Bricks is cheaper. At 25+ sites, Bricks Agency ($249/yr) costs less than Elementor Expert ($204/yr) while including every feature. And Bricks' $599 lifetime license breaks even with Elementor's unlimited tier in under two years.</div>

<h2 id="performance">⚡ Performance: where the gap is real</h2>
<p>This is the comparison's most measurable dimension. Both builders have improved in 2026, but they start from very different baselines.</p>

<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">Bricks performance</div><div style="font-size:14px; line-height:1.8;">
🏆 <b>Vue.js reactive editor</b> — no jQuery dependency<br>
🏆 <b>Clean HTML/CSS output</b> — minimal nested divs<br>
🏆 <b>No frontend jQuery</b> — pure vanilla JavaScript<br>
🏆 <b>CSS class system</b> — utility-class approach reduces inline styles<br>
🏆 <b>Native Gutenberg block rendering</b> — save Bricks layouts as WP blocks<br>
🏆 <b>Typical Lighthouse score:</b> 95–100 on well-built sites
</div></div>
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">Elementor performance</div><div style="font-size:14px; line-height:1.8;">
✅ <b>Editor V4 (Atomic)</b> — CSS-first foundation, reduces nested divs<br>
✅ <b>109ms TTFB</b> on optimized hosting (Elementor's own claim)<br>
⚠️ <b>More markup per element</b> than Bricks<br>
⚠️ <b>Inline styles by default</b> — bloats page weight on complex pages<br>
⚠️ <b>jQuery dependency</b> still present in some widgets<br>
⚠️ <b>Requires optimization effort</b> — lazy loading, asset cleanup, etc.
</div></div>
</div>

<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">⚡ The DOM size problem:</div>
<div class="ex-inner">Elementor generates more markup per element than Bricks. On a typical landing page with 20 widgets, this can mean 2–3x more DOM nodes and 30–50% more CSS. Core Web Vitals — particularly Largest Contentful Paint (LCP) and Cumulative Layout Shift (CLS) — are directly affected by DOM depth. Bricks' CSS class system means styling lives in a shared stylesheet rather than repeated inline, which reduces page weight as complexity grows.</div>
<div class="ex-inner-green">Elementor's Editor V4 (Atomic) addresses this directly with a CSS-first architecture, and the 109ms TTFB claim suggests meaningful improvement. But the gap between "improved" and "clean by default" remains real.</div>
</div>

<h2 id="ease">🎨 Ease of use: where Elementor wins</h2>
<p>If you have never built a WordPress site before, Elementor is easier to learn. The visual drag-and-drop interface, extensive template library and massive community mean you can find a tutorial for almost anything within minutes. Bricks has a steeper learning curve — particularly its CSS class system, which assumes familiarity with how CSS actually works.</p>

<div class="ex-grid">
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">Elementor ease of use</div><div style="font-size:14px; line-height:1.8;">
✅ <b>Lowest learning curve</b> in the WordPress builder space<br>
✅ <b>Massive template library</b> — 240+ importable kits<br>
✅ <b>Largest community</b> — tutorials, courses, Facebook groups everywhere<br>
✅ <b>Visual preview</b> — what you see is what you get, instantly<br>
✅ <b>Widget panel</b> — drag elements from a sidebar, configure in-context<br>
⚠️ <b>Feature bloat</b> — 85+ Pro widgets can overwhelm new users
</div></div>
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">Bricks ease of use</div><div style="font-size:14px; line-height:1.8;">
⚠️ <b>Steeper learning curve</b> — CSS class system requires CSS knowledge<br>
⚠️ <b>Smaller community</b> — fewer tutorials, courses and third-party resources<br>
✅ <b>Faster editor</b> — Vue.js reactivity means no lag when adding elements<br>
✅ <b>Less overwhelming</b> — 110+ elements but organized more logically<br>
✅ <b>Bricks Academy</b> — official documentation is thorough and well-structured<br>
⚠️ <b>No free trial</b> — you must purchase before you can test the editor
</div></div>
</div>

<div class="ex-card ex-card-neutral"><b>Assessment:</b> Elementor's ease-of-use advantage is real but narrowing. Bricks' editor is genuinely fast — the Vue.js framework means panels load instantly, elements snap into place without lag, and the builder never feels sluggish even on complex pages. The learning curve is steeper not because Bricks is harder to use, but because it encourages you to think in CSS classes rather than widget-level styling. For developers, this is a feature. For beginners, it is a barrier.</div>

<h2 id="features">⚙️ Features: what each builder includes</h2>
<p>Both builders cover the essentials — headers, footers, archive templates, WooCommerce pages, popups, forms and dynamic data. The differences are in depth and default inclusion.</p>

<div class="ex-grid">
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">Elementor features</div><div style="font-size:14px; line-height:1.8;">
✅ <b>85+ Pro widgets</b> (Advanced plans and up)<br>
✅ <b>Theme Builder</b> — headers, footers, archives, singles<br>
✅ <b>Form Builder</b> — multi-step, webhooks, CRM integrations<br>
✅ <b>Popup Builder</b> — triggered popups, slide-ins, full-screen<br>
✅ <b>WooCommerce Builder</b> — product pages, cart, checkout (Advanced+)<br>
✅ <b>AI features</b> — content generation, layout building, image creation<br>
✅ <b>240+ template kits</b> — importable full-site designs<br>
✅ <b>Motion effects</b> — parallax, scrolling, mouse tracking
</div></div>
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">Bricks features</div><div style="font-size:14px; line-height:1.8;">
✅ <b>110+ elements</b> — all included on every plan<br>
✅ <b>Theme Builder</b> — headers, footers, archives, singles, 404, search<br>
✅ <b>Form Builder</b> — with CRM routing natively<br>
✅ <b>Popup Builder</b> — included on every plan<br>
✅ <b>WooCommerce Builder</b> — all shop templates, included on every plan<br>
✅ <b>Menu Builder</b> — mega menus, mobile menus, offcanvas<br>
✅ <b>Query Loop Builder</b> — visual database queries with load-more, infinite scroll<br>
✅ <b>Interactions engine</b> — triggers and actions without third-party plugins<br>
✅ <b>Custom breakpoints</b> — unlimited responsive breakpoints, desktop and mobile-first
</div></div>
</div>

<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">🏆 Feature verdict:</div>
<div class="ex-inner">Bricks includes everything on every plan — there is no feature gating between Starter and Ultimate. Elementor gates its most useful features (WooCommerce Builder, Popup Builder, full widget set) behind the Advanced Solo tier ($84/yr) or higher. For agencies managing many sites, Bricks' all-inclusive approach means predictable feature availability across the portfolio.</div>
<div class="ex-inner-cyan">Elementor's advantage is ecosystem breadth: 240+ template kits, a massive third-party addon market (Ultimate Addons, Essential Addons, etc.), and AI features that Bricks does not yet match. If you need a template for almost any industry, Elementor's library is deeper.</div>
</div>

<h2 id="dev">🔧 Developer features: CSS classes, dynamic data and hooks</h2>
<p>This is where the builders diverge most sharply. Bricks treats CSS classes as a first-class citizen — you define utility classes once and apply them globally, similar to Tailwind CSS. Elementor's styling is primarily widget-level and inline, with global colors and fonts as the shared layer.</p>

<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">Bricks developer features</div><div style="font-size:14px; line-height:1.8;">
✅ <b>CSS Classes and Variables</b> — define once, apply everywhere<br>
✅ <b>Native Gutenberg block rendering</b> — save Bricks layouts as WP blocks<br>
✅ <b>External API fetching</b> — query REST APIs directly from the builder<br>
✅ <b>Query Loop Builder</b> — visual + PHP query support<br>
✅ <b>ACF, Meta Box, Pods, Toolset</b> — native dynamic data integration<br>
✅ <b>Custom elements</b> — create your own builder elements<br>
✅ <b>Pseudo-classes/elements</b> — :hover, ::before, ::after in the builder<br>
✅ <b>Password protection</b> — built-in content locking
</div></div>
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">Elementor developer features</div><div style="font-size:14px; line-height:1.8;">
✅ <b>Custom CSS and code</b> — per-element CSS, custom HTML<br>
✅ <b>Dynamic content</b> — post data, custom fields (ACF on Advanced+)<br>
✅ <b>Form webhooks</b> — send form data to external APIs<br>
✅ <b>Theme Builder conditions</b> — target specific posts, archives, etc.<br>
⚠️ <b>No native CSS class system</b> — styling is primarily inline<br>
⚠️ <b>ACF integration requires Advanced Solo+</b> ($84/yr minimum)<br>
⚠️ <b>No native query loop</b> — requires Elementor Pro's Loop Builder (limited)<br>
✅ <b>Global settings</b> — site-wide colors, fonts, spacing
</div></div>
</div>

<div class="ex-card ex-card-neutral"><b>Developer verdict:</b> Bricks is the more powerful tool for developers who think in CSS. The class system, pseudo-element support, native Gutenberg block rendering and external API fetching are features Elementor does not offer at any price. Elementor's developer features are solid but require the Advanced Solo tier ($84/yr) to unlock ACF integration and the full widget set — features Bricks includes on its $79 Starter plan.</div>

<h2 id="scenarios">🧭 Which should you choose? Four scenarios</h2>
<div class="ex-grid">
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">🎯 "I am a beginner building my first WordPress site."</div><div style="font-size:14px; line-height:1.6;">Choose <b>Elementor</b>. The visual drag-and-drop interface, 240+ template kits and massive community make it the fastest path from zero to a published site. Start with the free version, upgrade to Essential ($59/yr) when you need Theme Builder, and upgrade further only when WooCommerce or Popups become necessary.</div></div>
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">⚡ "Performance is my top priority — I build speed-critical sites."</div><div style="font-size:14px; line-height:1.6;">Choose <b>Bricks</b>. Clean HTML/CSS output, no jQuery dependency, CSS class system and Vue.js editor produce measurably faster pages. The $79 Starter plan includes every feature — no upgrade path needed for WooCommerce, popups or forms.</div></div>
<div class="ex-card ex-card-violet"><div style="font-weight:700; margin-bottom:8px;">🔧 "I am a developer who thinks in CSS and wants full control."</div><div style="font-size:14px; line-height:1.6;">Choose <b>Bricks</b>. CSS Classes and Variables, pseudo-elements, native Gutenberg block rendering, external API fetching and the Query Loop Builder give you developer-grade control that Elementor cannot match. The $599 lifetime license pays for itself within two years of agency work.</div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:8px;">💼 "I manage 25+ client sites and need predictable costs."</div><div style="font-size:14px; line-height:1.6;">Choose <b>Bricks Agency</b> ($249/yr unlimited) or <b>Bricks Ultimate</b> ($599 lifetime). Elementor's Expert tier ($204/yr for 25 sites) is cheaper per-site but lacks the full widget set without upgrading to Advanced Solo per site. Bricks' all-inclusive model means every client site gets every feature at a flat rate.</div></div>
</div>

<h2 id="faq">❓ Frequently asked questions</h2>
<div class="ex-card ex-card-neutral"><b>Can I migrate from Elementor to Bricks (or vice versa)?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Migration is possible but not trivial. Moving from Elementor to Bricks means rebuilding every template in Bricks' editor — there is no automated migration tool. Moving from Bricks to Elementor requires the same rebuild in Elementor. For large sites, plan 2–4 weeks of rebuild time. The safest approach: build new pages in the target builder while keeping existing pages on the current builder until the migration is complete.</div></div>
<div class="ex-card ex-card-neutral"><b>Does Bricks have a free version?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">No. Bricks requires a paid license ($79/yr minimum) to use. There is no free tier on WordPress.org. However, the 60-day money-back guarantee effectively gives you two months to evaluate the builder risk-free. Elementor's free version is genuinely useful for basic page building, which lowers the barrier to entry significantly.</div></div>
<div class="ex-card ex-card-neutral"><b>Which builder produces cleaner code?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Bricks. Its output is leaner HTML with fewer nested divs, less inline CSS and no jQuery dependency. Elementor's Editor V4 (Atomic) update in 2026 reduced markup significantly, but the output is still heavier than Bricks on equivalent pages. For SEO and Core Web Vitals, cleaner code translates to faster rendering, smaller page weights and better Lighthouse scores.</div></div>
<div class="ex-card ex-card-neutral"><b>Do both builders support WooCommerce?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Yes, but the inclusion differs. Bricks includes its full WooCommerce Builder on every plan — Starter, Business, Agency and Ultimate. Elementor gates WooCommerce Builder behind the Advanced Solo tier ($84/yr) or higher. The Essential plan ($59/yr) does not include WooCommerce widgets. Both builders let you design single product pages, archive pages, cart and checkout.</div></div>
<div class="ex-card ex-card-neutral"><b>Which builder has a larger community and more third-party addons?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Elementor, by a wide margin. With 22M+ active sites, Elementor has the largest WordPress page builder community in the world. Third-party addon ecosystems (Ultimate Addons, Essential Addons, PowerPack, etc.) offer hundreds of additional widgets. Bricks' community is growing rapidly but is smaller — its addon ecosystem is curated rather than sprawling. For tutorials, courses and community support, Elementor has more available resources.</div></div>

<h2 id="verdict">🔎 Final verdict</h2>
<div class="ex-summary-bg">
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:16px;">
<div class="ex-summary-card" style="border-left:4px solid #06b6d4;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">⚡ Bricks Builder — 9.0</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The performance king: clean code output, CSS class system, Vue.js editor speed, all features on every plan, and a $599 lifetime license that breaks even with Elementor in under two years.</div><div class="ex-inner-cyan">The developer's choice for WordPress sites that need to be fast.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #8b5cf6;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🎨 Elementor — 8.6</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The ecosystem giant: 22M+ sites, the lowest learning curve, 240+ template kits, AI features and the largest community. Editor V4 (Atomic) is closing the performance gap.</div><div class="ex-inner-violet">The beginner's choice for WordPress sites that need to be built fast.</div></div>
</div>
</div>

<div class="ex-card ex-card-green-strong"><div style="font-weight:700;">The honest summary: <b>Elementor wins on accessibility.</b> If you have never built a WordPress site, Elementor's visual interface, massive template library and tutorials-everywhere community make it the fastest path to a published site. <b>Bricks wins on everything else.</b> Performance, code quality, pricing at scale, developer features, CSS class management and the all-inclusive feature model produce a more professional result for less money — if you are willing to invest the time to learn it. The two builders are converging (Elementor's Editor V4 reduces bloat, Bricks' community grows weekly), but the gap in philosophy remains: Elementor optimizes for speed of creation, Bricks optimizes for speed of rendering.</div></div>

<div style="background:#0f172a; color:#fff; padding:22px; border-radius:14px; text-align:center;">
<div style="font-size:24px; font-weight:700; margin-bottom:12px;">The best builder is the one that makes your sites faster</div>
<div style="font-size:16px; line-height:1.6; opacity:0.95; max-width:720px; margin:0 auto;">
Page builders are tools, not identities. Use Elementor if it gets you building faster. Use Bricks if it gets your sites loading faster. The visitors do not care which builder you chose — they care whether the page loaded in 1 second or 4.
<div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.2);">Explore the tool cards above — both builders have full profiles with criteria scores on this site.</div>
</div>
</div>
HTML;
}
