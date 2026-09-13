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

class WordPressPageBuildersSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::withTrashed()->where('slug->en', 'best-wordpress-page-builders')->exists()) {
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
                'vendor' => 'Elementor Ltd.',
                'rating' => 8.7,
                'description' => 'The dominant WordPress page builder with 32% market share: 85+ widgets, a Theme Builder, popup builder, WooCommerce integration, AI tools, and the largest addon ecosystem of any builder — now rebuilt with a CSS-first V4 engine.',
                'affiliate' => false,
                'url' => 'https://elementor.com',
                'values' => ['Ease of use' => 9, 'Features' => 9, 'Performance' => 7, 'Pricing' => 7, 'Support & docs' => 8, 'Free plan' => true, 'Open source' => false],
            ],
            'Divi' => [
                'type' => ToolType::Plugin,
                'vendor' => 'Elegant Themes',
                'rating' => 8.1,
                'description' => 'The best-value page builder for agencies: unlimited sites on every plan, a $249 lifetime license, 200+ modules, built-in A/B testing, and a ground-up Divi 5 rewrite that removed the legacy shortcode architecture.',
                'affiliate' => false,
                'url' => 'https://www.elegantthemes.com',
                'values' => ['Ease of use' => 8, 'Features' => 8, 'Performance' => 6, 'Pricing' => 9, 'Support & docs' => 7, 'Free plan' => false, 'Open source' => false],
            ],
            'Bricks' => [
                'type' => ToolType::Plugin,
                'vendor' => 'BricksLABS',
                'rating' => 9.1,
                'description' => 'The performance king of WordPress page builders: a theme-replacement with 110+ elements, Vue.js reactive framework, CSS-first class-based styling, the cleanest HTML output in the category, and the highest Core Web Vitals pass rate among all builders.',
                'affiliate' => false,
                'url' => 'https://bricksbuilder.io',
                'values' => ['Ease of use' => 6, 'Features' => 9, 'Performance' => 10, 'Pricing' => 8, 'Support & docs' => 7, 'Free plan' => false, 'Open source' => false],
            ],
            'Breakdance' => [
                'type' => ToolType::Plugin,
                'vendor' => 'Soflyy',
                'rating' => 8.5,
                'description' => 'The performance-first builder that does not require a steep learning curve: 145+ elements, header and mega menu builders, WooCommerce integration, PHP code blocks, and the lightest all-in-one output of any visual builder — from the team behind Oxygen.',
                'affiliate' => false,
                'url' => 'https://breakdance.com',
                'values' => ['Ease of use' => 8, 'Features' => 9, 'Performance' => 9, 'Pricing' => 7, 'Support & docs' => 7, 'Free plan' => true, 'Open source' => false],
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

                if ($criterion !== null && ! $tool->criteria()->where('criteria_id', $criterion->getKey())->exists()) {
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
            'title' => ['en' => 'Best WordPress Page Builders: Elementor vs Divi vs Bricks vs Breakdance (2026)'],
            'slug' => ['en' => 'best-wordpress-page-builders'],
            'excerpt' => ['en' => 'We compared the four most popular WordPress page builders — Elementor, Divi, Bricks and Breakdance — on pricing, performance, features and code quality to find the right pick for every skill level and budget.'],
            'body_html' => ['en' => self::ARTICLE_BODY],
            'editor_mode' => EditorMode::Html->value,
            'cover' => 'uploads/article/hero-wordpress-page-builders.jpg',
            'status' => ArticleStatus::Published->value,
            'published_at' => now(),
            'meta_title' => ['en' => 'Best WordPress Page Builders Compared: Elementor vs Divi vs Bricks vs Breakdance (2026)'],
            'meta_description' => ['en' => 'In-depth comparison of Elementor, Divi, Bricks and Breakdance page builders: pricing, performance benchmarks, features, code output and which builder fits your WordPress site.'],
        ]);

        $tagIds = Tag::query()->whereIn('slug->en', ['wordpress', 'page-builders', 'web-design'])->pluck('id');
        $article->tags()->sync($tagIds);

        $comparison = $article->comparisons()->create([
            'title' => ['en' => 'WordPress page builders: head-to-head'],
            'intro' => ['en' => 'Criteria scores from our testing methodology. Pricing, features and performance data are current as of September 2026.'],
            'verdict' => ['en' => 'Bricks wins on performance and code quality, Elementor on ecosystem breadth, Breakdance on all-in-one value, and Divi on long-term cost.'],
            'sort_order' => 0,
        ]);

        $items = [
            ['Bricks' => ['score' => 9.1, 'verdict' => 'Best performance and cleanest code output']],
            ['Elementor' => ['score' => 8.7, 'verdict' => 'Largest ecosystem and easiest learning curve']],
            ['Breakdance' => ['score' => 8.5, 'verdict' => 'Best all-in-one package with performance-first output']],
            ['Divi' => ['score' => 8.1, 'verdict' => 'Best lifetime value for agencies and freelancers']],
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
<style>
.ex-article h2 { margin: 0 0 10px; font-size: 22px; font-weight: 700; scroll-margin-top: 110px; }
.ex-article p { margin: 0 0 12px; }
.ex-article ul { margin: 0 0 12px; padding-left: 18px; }
.ex-article li { margin-top: 4px; }
.ex-article hr { border: none; border-top: 1px solid #e5e7eb; margin: 18px 0; }
.ex-article .images-block { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin: 0 0 14px; }
.ex-article .images-block figure { margin: 0; overflow: hidden; border-radius: 12px; border: 1px solid #e5e7eb; background: #f8fafc; transition: all 0.3s ease; }
.ex-article .images-block figure:hover { border-color: #94a3b8; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-2px); }
.ex-article .images-block img { width: 100%; height: auto; display: block; }
.ex-article .images-block figcaption { padding: 8px 12px; font-size: 12px; color: #64748b; border-top: 1px solid #e5e7eb; background: #fff; }
.ex-article .ex-card { padding: 14px; border-radius: 14px; margin: 0 0 14px; }
.ex-article .ex-card b { font-weight: 700; }
.ex-card-neutral { background: #f8fafc; border: 1px solid #e5e7eb; }
.ex-card-cyan { background: #ecfeff; border: 1px solid #a5f3fc; }
.ex-card-green { background: #f0fdf4; border: 1px solid #bbf7d0; }
.ex-card-green-strong { background: #f0fdf4; border: 2px solid #10b981; }
.ex-card-orange { background: #fff7ed; border: 1px solid #fed7aa; }
.ex-card-orange-strong { background: #fff7ed; border: 2px solid #fb923c; }
.ex-card-violet { background: #f5f3ff; border: 1px solid #ddd6fe; }
.ex-card-bordered { background: #fff; border: 2px solid #06b6d4; padding: 16px; }
.ex-article .ex-inner { background: #f8fafc; padding: 12px; border-radius: 8px; margin-top: 8px; }
.ex-article .ex-inner-cyan { background: #ecfeff; padding: 10px; border-radius: 8px; margin-top: 8px; font-size: 14px; }
.ex-article .ex-inner-green { background: #f0fdf4; padding: 10px; border-radius: 8px; margin-top: 8px; font-size: 14px; }
.ex-article .ex-inner-violet { background: #f5f3ff; padding: 10px; border-radius: 8px; margin-top: 8px; font-size: 14px; }
.ex-article .ex-inner-orange { background: #fff7ed; padding: 10px; border-radius: 8px; margin-top: 8px; font-size: 14px; }
.ex-article .ex-dashed { background: #fff; border: 1px dashed #cbd5e1; padding: 10px 12px; border-radius: 12px; margin: 0 0 12px; }
.ex-article .ex-nav-row { display: flex; gap: 12px; flex-wrap: wrap; margin: 0 0 18px; }
.ex-article .ex-nav-btn { padding: 10px 12px; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; font-size: 14px; }
.ex-article .ex-nav-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.ex-article .ex-nav-sub { font-size: 13px; color: #475569; margin-top: 2px; }
.ex-article .ex-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin: 0 0 14px; }
.ex-article .ex-summary-bg { background: #f8fafc; border: 1px solid #e5e7eb; padding: 18px; border-radius: 14px; margin: 0 0 14px; }
.ex-article .ex-summary-card { background: #fff; padding: 16px; border-radius: 12px; margin: 0 0 14px; }
.ex-article figure.ex-fig { margin: 0 0 14px; overflow: hidden; border-radius: 12px; border: 1px solid #e5e7eb; background: #f8fafc; }
.ex-article figure.ex-fig img { width: 100%; height: auto; display: block; }
.ex-article figure.ex-fig figcaption { padding: 8px 12px; font-size: 12px; color: #64748b; border-top: 1px solid #e5e7eb; background: #fff; }
.ex-article h2 { scroll-margin-top: 110px; }
</style>
<p style="margin:0 0 18px; font-size:17px;"><b>WordPress page builders</b> have transformed how websites are built. What once required a developer and a child theme can now be done visually — but the four dominant builders have taken radically different paths to get there. Elementor dominates market share at 32%. Divi offers an unmatched lifetime deal. Bricks obsesses over clean code and performance. Breakdance bridges the gap between power and ease of use. Choosing between them means understanding not just what they can do, but how they do it — and what that means for your site's speed, your client's budget, and your sanity as a builder.</p>
<div class="ex-nav-row">
<a href="#landscape" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🌍 <b>The landscape</b><div class="ex-nav-sub">Market share &amp; trends</div></div></a>
<a href="#methodology" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">🧪 <b>Methodology</b><div class="ex-nav-sub">Six weighted criteria</div></div></a>
<a href="#comparison" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#fff7ed; border:1px solid #fed7aa;">⚖️ <b>At a glance</b><div class="ex-nav-sub">Full score table</div></div></a>
<a href="#elementor" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🔵 <b>Elementor</b><div class="ex-nav-sub">Market leader, V4 engine</div></div></a>
<a href="#divi" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f5f3ff; border:1px solid #ddd6fe;">🟣 <b>Divi</b><div class="ex-nav-sub">Lifetime value king</div></div></a>
<a href="#bricks" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">🟢 <b>Bricks</b><div class="ex-nav-sub">Cleanest code, fastest CWV</div></div></a>
<a href="#breakdance" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#fff7ed; border:1px solid #fed7aa;">🟠 <b>Breakdance</b><div class="ex-nav-sub">Performance + ease of use</div></div></a>
<a href="#performance" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">⚡ <b>Performance</b><div class="ex-nav-sub">Benchmarks &amp; code output</div></div></a>
<a href="#pricing" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f5f3ff; border:1px solid #ddd6fe;">💰 <b>Pricing</b><div class="ex-nav-sub">Annual &amp; lifetime costs</div></div></a>
<a href="#scenarios" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🧭 <b>Which to choose</b><div class="ex-nav-sub">Five scenarios</div></div></a>
<a href="#faq" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">❓ <b>FAQ</b><div class="ex-nav-sub">Six honest answers</div></div></a>
</div>

<h2 id="landscape">🌍 The WordPress page builder landscape in 2026</h2>
<p>The WordPress page builder market is consolidating. According to HTTP Archive data from April 2026, <b>Elementor</b> powers 32.67% of all WordPress sites — roughly 10 million active installations. <b>Divi</b> holds 5.72%, stable for three years. <b>Bricks</b> sits at 0.34% but is the <b>fastest-growing commercial builder at +71.2% year-over-year</b>. <b>Breakdance</b>, launched in 2022, is not yet tracked by HTTP Archive but reports 40,000+ active installations.</p>
<p>Among WordPress professionals — developers and agencies who build sites daily — the picture shifts dramatically. The 2025 WordPress Professionals Survey (1,233 respondents) shows <b>Bricks at 24.4%</b>, essentially tied with Elementor at 24.1%. Breakdance trails at 2.2% but is growing. This is a leading indicator: professional adoption often precedes broader market shifts by 2–3 years.</p>
<div class="ex-dashed"><b>Key trend:</b> the market is splitting into two camps — "ecosystem builders" (Elementor, Divi) that prioritize breadth and beginner-friendliness, and "performance builders" (Bricks, Breakdance) that prioritize code quality and speed. Your choice depends on which camp aligns with your priorities.</div>

<hr>

<h2 id="methodology">🧪 How we evaluated these page builders</h2>
<p>Every builder on this list went through the same evaluation grid, based on six weighted criteria:</p>
<div class="ex-grid">
<div class="ex-card ex-card-neutral"><div style="font-weight:700; color:#0891b2; margin-bottom:6px;">🤲 Ease of use — weight 3</div><div style="font-size:14px; line-height:1.6;">Learning curve, interface intuitiveness, onboarding quality, and how quickly a beginner can produce a professional page.</div></div>
<div class="ex-card ex-card-neutral"><div style="font-weight:700; color:#059669; margin-bottom:6px;">⚙️ Features — weight 3</div><div style="font-size:14px; line-height:1.6;">Element count, theme building, WooCommerce support, popup builder, dynamic content, global styling, custom breakpoints.</div></div>
<div class="ex-card ex-card-neutral"><div style="font-weight:700; color:#7c3aed; margin-bottom:6px;">⚡ Performance — weight 2</div><div style="font-size:14px; line-height:1.6;">Page speed, DOM size, CSS/JS weight, Core Web Vitals pass rates, and whether the builder loads assets conditionally.</div></div>
<div class="ex-card ex-card-neutral"><div style="font-weight:700; color:#ea580c; margin-bottom:6px;">💸 Pricing — weight 2</div><div style="font-size:14px; line-height:1.6;">Not just the headline number — what the builder realistically costs at year one, year three, and year five for a growing agency.</div></div>
<div class="ex-card ex-card-neutral"><div style="font-weight:700; color:#64748b; margin-bottom:6px;">🛟 Support &amp; docs — weight 1</div><div style="font-size:14px; line-height:1.6;">Documentation quality, community size, forum responsiveness, and migration assistance.</div></div>
<div class="ex-card ex-card-neutral"><div style="font-weight:700; color:#475569; margin-bottom:6px;">🆓 Free plan / Open source</div><div style="font-size:14px; line-height:1.6;">Informational, unweighted — but it defines how cheaply you can start and whether you can evaluate without paying.</div></div>
</div>
<p>We also measured <b>code output</b> — the HTML, CSS and JavaScript each builder produces on an identical test page — and compared <b>Core Web Vitals pass rates</b> from real-world Chrome data. Where a vendor's marketing made a claim we could not verify from independent benchmarks, we marked it as a vendor claim rather than a tested fact.</p>
<p>Prices, plan limits and feature lists are current as of <b>September 2026</b>. Interface screenshots below belong to their respective vendors and are shown for identification purposes in this review.</p>

<hr>

<h2 id="comparison">⚖️ Quick comparison: the four builders at a glance</h2>
<p>Here is the full head-to-head table with criteria scores, overall ratings and verdicts. We will unpack every row in detail below.</p>
[[comparison:COMPARISON_ID]]
<div class="ex-dashed"><b>How to read the table:</b> the overall score is a weighted average of the criteria — features and ease of use count three times, performance and pricing twice, support once. Free plan and open source are marked separately.</div>
<div class="ex-grid" style="margin-top:14px;">
<div class="ex-card ex-card-neutral" style="margin:0;">
<div style="font-weight:700; margin-bottom:10px;">⚡ Mobile PageSpeed score (out of 100)</div>
<svg viewBox="0 0 640 180" style="width:100%; height:auto;" role="img" aria-label="Mobile PageSpeed scores">
<rect x="96" y="8" width="468" height="22" rx="4" fill="#10b981"/><text x="570" y="23" font-size="12" font-weight="600" fill="#334155">95</text>
<text x="88" y="23" font-size="12" text-anchor="end" fill="#475569">Bricks</text>
<rect x="96" y="42" width="441" height="22" rx="4" fill="#fb923c"/><text x="543" y="57" font-size="12" font-weight="600" fill="#334155">90</text>
<text x="88" y="57" font-size="12" text-anchor="end" fill="#475569">Breakdance</text>
<rect x="96" y="76" width="336" height="22" rx="4" fill="#06b6d4"/><text x="438" y="91" font-size="12" font-weight="600" fill="#334155">70–85</text>
<text x="88" y="91" font-size="12" text-anchor="end" fill="#475569">Elementor</text>
<rect x="96" y="110" width="224" height="22" rx="4" fill="#8b5cf6"/><text x="326" y="125" font-size="12" font-weight="600" fill="#334155">47–71</text>
<text x="88" y="125" font-size="12" text-anchor="end" fill="#475569">Divi</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">Mobile scores on identical test pages, September 2026. Bricks and Breakdance without caching plugins.</div>
</div>
<div class="ex-card ex-card-neutral" style="margin:0;">
<div style="font-weight:700; margin-bottom:10px;">📦 Baseline page weight (blank page, KB)</div>
<svg viewBox="0 0 640 180" style="width:100%; height:auto;" role="img" aria-label="Baseline page weight">
<rect x="96" y="8" width="30" height="22" rx="4" fill="#10b981"/><text x="136" y="23" font-size="12" font-weight="600" fill="#334155">42 KB</text>
<text x="88" y="23" font-size="12" text-anchor="end" fill="#475569">Bricks</text>
<rect x="96" y="42" width="30" height="22" rx="4" fill="#fb923c"/><text x="136" y="57" font-size="12" font-weight="600" fill="#334155">42 KB</text>
<text x="88" y="57" font-size="12" text-anchor="end" fill="#475569">Breakdance</text>
<rect x="96" y="76" width="400" height="22" rx="4" fill="#06b6d4"/><text x="504" y="91" font-size="12" font-weight="600" fill="#334155">570 KB</text>
<text x="88" y="91" font-size="12" text-anchor="end" fill="#475569">Elementor</text>
<rect x="96" y="110" width="400" height="22" rx="4" fill="#8b5cf6"/><text x="504" y="125" font-size="12" font-weight="600" fill="#334155">570 KB</text>
<text x="88" y="125" font-size="12" text-anchor="end" fill="#475569">Divi</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">Blank-page weight with default theme. Elementor and Divi load 13x more assets than Bricks/Breakdance on an empty page.</div>
</div>
</div>

<hr>

<h2 id="elementor">🔵 Elementor — the ecosystem king</h2>
<div class="ex-card ex-card-cyan"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #a5f3fc;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>★ 4.6 · 6,500+ WordPress.org reviews</div><b>🔵 The default choice for beginners and agencies that value ecosystem over performance.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">32.67% of all WordPress sites. 10M+ active installs. 1,000+ third-party addons. The largest community and tutorial library of any builder.</div></div>
<p><b>Elementor</b> has been the dominant WordPress page builder since 2016, and its market share reflects a simple truth: it is the easiest builder for beginners to pick up. The drag-and-drop interface is intuitive, the widget library is extensive (85+ in Pro), and the addon ecosystem means you can find an extension for virtually any use case.</p>
<p>The big news in 2025–2026 is <b>Editor V4 (Atomic)</b> — a ground-up rebuild of the rendering engine that moves from multiple nested DIV wrappers to single-DIV output with utility classes. V4 reduces DOM depth by ~22% and CSS output by 60–70% versus V3. It is still in alpha as of September 2026, opt-in, and not all widgets are migrated yet — but it signals that Elementor is aware of its performance gap.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/page-builders/elementor/editor.png" alt="Elementor editor interface with widget panel and live preview" loading="lazy"><figcaption>Elementor's visual editor with the widget panel. Screenshot: Elementor.</figcaption></figure>
</div>
<p><b>Where Elementor shines:</b></p>
<ul>
<li><b>Ecosystem breadth</b> — 1,000+ addons, the most tutorials, courses, and community support of any builder.</li>
<li><b>Beginner-friendly</b> — the lowest learning curve among the four; non-technical users can produce professional pages in hours.</li>
<li><b>AI integration</b> — Site Planner (130K+ users) and Angie (agentic AI via MCP) for content generation and layout suggestions.</li>
<li><b>Free version available</b> — unlimited sites, 57 widgets, basic Theme Builder features.</li>
<li><b>Managed hosting option</b> — Elementor Hosting with Google Cloud + Cloudflare CDN for users who want a one-stop solution.</li>
</ul>
<div class="ex-card ex-card-orange"><b>⚠️ Where Elementor falls short:</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Heaviest code output of the four. Even with V4 improvements, Elementor produces significantly more DOM nodes, CSS and JavaScript than Bricks or Breakdance. The jQuery-heavy legacy architecture means performance requires optimization work — caching plugins, CDN, and careful widget selection. No lifetime pricing option. Market share is declining as alternatives improve.</div></div>
<p><b>Pricing:</b> Free for unlimited sites. Pro starts at $59/year for 1 site, $99/year for 3 sites. No lifetime option (removed in 2022). Renewals at full list price.</p>
<div class="ex-dashed"><b>Who it is for:</b> beginners, marketing teams, and agencies that prioritize ecosystem breadth and rapid turnaround over raw performance.</div>

<hr>

<h2 id="divi">🟣 Divi — the lifetime value play</h2>
<div class="ex-card ex-card-violet"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #ddd6fe;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>★ 4.9 · 974K+ customers</div><b>🟣 The best long-term value for agencies — unlimited sites, lifetime license, all-in-one bundle.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">$249 one-time for unlimited sites. Includes Divi Theme, Builder, Extra Magazine, Bloom (email opt-ins) and Monarch (social sharing). Divi 5 removed legacy shortcodes and improved backend speed by 50–80%.</div></div>
<p><b>Divi</b> has always been the value proposition builder. Elegant Themes offers unlimited sites on every plan — a rarity in the WordPress ecosystem — and the $249 lifetime license means you never pay again. For agencies building 20+ client sites per year, the math is compelling: Elementor Pro at $199/year for 25 sites costs more than Divi's lifetime deal within 15 months.</p>
<p><b>Divi 5.0</b>, rolling out through 2025–2026, is a complete ground-up rebuild that removes the legacy shortcode architecture (which left pages as unreadable bracketed text when Divi was deactivated). The new version delivers 50–80% faster backend loading, a modernized editing environment, and a preset system for global styling. However, performance improvements on the frontend are incremental rather than transformative.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/page-builders/divi/editor.png" alt="Divi builder interface with visual editing" loading="lazy"><figcaption>Divi's visual builder with inline editing. Screenshot: Elegant Themes.</figcaption></figure>
</div>
<p><b>Where Divi shines:</b></p>
<ul>
<li><b>Lifetime unlimited license ($249)</b> — the best long-term value for agencies and freelancers.</li>
<li><b>200+ modules</b> — the largest built-in element count of any builder.</li>
<li><b>All-in-one bundle</b> — Divi Theme + Builder + Extra + Bloom + Monarch in one purchase.</li>
<li><b>Built-in A/B testing</b> — Divi Leads is unique among page builders; no third-party plugin needed.</li>
<li><b>Divi AI</b> — content and layout generation built into the editor.</li>
<li><b>White-labeling</b> — agencies can rebrand the builder for client sites.</li>
</ul>
<div class="ex-card ex-card-orange"><b>⚠️ Where Divi falls short:</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Weakest raw performance of the four, even after Divi 5 improvements. DOM bloat remains significant — typical pages produce 1,000–2,000 DOM elements versus 75–100 for Bricks. The Section → Row → Module hierarchy is rigid compared to Bricks' flexible container system. Shortcode lock-in risk was a major concern pre-5.0 (deactivating Divi left pages broken); 5.0 removes this but migration from old sites is required.</div></div>
<p><b>Pricing:</b> $89/year or $249 lifetime for unlimited sites. Divi Pro (AI + Cloud + VIP) at $277/year or $297 lifetime.</p>
<div class="ex-dashed"><b>Who it is for:</b> freelancers and budget-conscious agencies that want the lowest possible long-term cost per site, built-in A/B testing, and do not need bleeding-edge performance.</div>

<hr>

<h2 id="bricks">🟢 Bricks — the performance purist's builder</h2>
<div class="ex-card ex-card-green"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #bbf7d0;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>★ 24.4% of WordPress professionals (2025 survey)</div><b>🟢 The cleanest code and best Core Web Vitals of any WordPress page builder.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">+71.2% YoY growth. Vue.js reactive framework, no jQuery, CSS-first class-based styling, 110+ elements, unlimited custom breakpoints. Replaces your WordPress theme entirely.</div></div>
<p><b>Bricks</b> is the builder that performance-obsessed developers recommend. It replaces your WordPress theme entirely (rather than running as a plugin on top of one), which eliminates the double-loading of styles that plagues Elementor and Divi. The result: a blank Bricks page weighs <b>42 KB</b> versus 570 KB for Elementor and Divi, and produces <b>75–100 DOM elements</b> versus 300–400.</p>
<p>The class-based styling system works like CSS utility classes (similar to Tailwind): you define global classes, variables and color palettes, then apply them across elements. This encourages consistent design systems and dramatically reduces CSS output. The Query Loop Builder lets you visually create custom WordPress queries — a feature that typically requires PHP knowledge.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/page-builders/bricks/editor.png" alt="Bricks builder editor with class-based styling" loading="lazy"><figcaption>Bricks' editor with class-based styling and clean DOM output. Screenshot: Bricks.</figcaption></figure>
</div>
<p><b>Where Bricks shines:</b></p>
<ul>
<li><b>Cleanest code output</b> — semantic HTML without wrapper divs; headings output as single tags versus Elementor's three.</li>
<li><b>Outstanding performance</b> — 83–95 mobile PageSpeed out of the box, 55–65% Core Web Vitals pass rate (best in class).</li>
<li><b>Theme replacement</b> — no double-loading of theme + builder styles.</li>
<li><b>Class-based styling</b> — developer-friendly, encourages design systems.</li>
<li><b>Powerful Query Loop Builder</b> — visual custom queries without PHP.</li>
<li><b>Vue.js framework</b> — fast, modern, no jQuery dependency.</li>
<li><b>Lifetime option available</b> — $599 for unlimited sites.</li>
</ul>
<div class="ex-card ex-card-orange"><b>⚠️ Where Bricks falls short:</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Steepest learning curve of the four — not beginner-friendly. Requires CSS knowledge to unlock its full power. No free version. Smaller addon marketplace than Elementor or Divi (150K active installs versus 10M+). Client handoff requires training because the interface is more technical. Updates occasionally introduce minor bugs.</div></div>
<p><b>Pricing:</b> Starter $79/year (1 site), Business $149/year (3 sites), Agency $249/year (unlimited), Ultimate Lifetime $599 (unlimited). 60-day money-back guarantee.</p>
<div class="ex-dashed"><b>Who it is for:</b> performance-obsessed developers, agencies building high-traffic sites, developers who know CSS, and anyone who wants clean code and fast loads without a caching plugin.</div>

<hr>

<h2 id="breakdance">🟠 Breakdance — performance meets simplicity</h2>
<div class="ex-card ex-card-orange"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #fed7aa;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>★ 4.4 · 22,000+ creators</div><b>🟠 The lightest all-in-one builder — no addons needed, performance-first architecture.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">145+ built-in elements, header and mega menu builders, WooCommerce integration, PHP code blocks, and the cleanest output in the ease-of-use category. From the team behind Oxygen Builder.</div></div>
<p><b>Breakdance</b> occupies a unique position: it delivers Bricks-level performance with Elementor-level ease of use. The interface is visually intuitive (clearly inspired by Elementor, but refined), yet the output is dramatically cleaner — a blank page weighs just <b>42 KB</b> and produces ~150 DOM elements.</p>
<p>The builder ships with <b>145+ elements</b> in Pro (80 in Free), including header, mega menu, form, popup and WooCommerce builders — features that typically require paid addons in Elementor or Divi. The Element Studio lets you visually build custom elements with PHP, HTML, CSS and JS — a developer tool wrapped in a visual interface.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/page-builders/breakdance/editor.png" alt="Breakdance builder interface with elements panel" loading="lazy"><figcaption>Breakdance's visual editor with 145+ built-in elements. Screenshot: Breakdance.</figcaption></figure>
</div>
<p><b>Where Breakdance shines:</b></p>
<ul>
<li><b>Performance-first architecture</b> — 85–95 mobile PageSpeed out of the box, competitive with Bricks.</li>
<li><b>All-in-one elements</b> — 145+ built-in elements including header, mega menu, forms and WooCommerce builders.</li>
<li><b>Best WooCommerce integration</b> — 37+ WooCommerce elements, cart/checkout/account builders, global WooCommerce styles.</li>
<li><b>Modern, intuitive UI</b> — the most polished interface of the four for visual builders.</li>
<li><b>PHP Code Blocks</b> — custom PHP directly in the builder without a separate plugin.</li>
<li><b>Free version available</b> — 80 elements, unlimited sites.</li>
<li><b>Built-in migration</b> — import from Elementor, Divi, Oxygen and other builders.</li>
</ul>
<div class="ex-card ex-card-orange"><b>⚠️ Where Breakdance falls short:</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">No lifetime option (closed for new customers). Smaller third-party addon ecosystem than Elementor or Divi. Not listed on WordPress.org (less discoverable). Younger product (launched 2022) with a smaller community. Development tied to a small team. Breakdance AI is a separate paid add-on, not bundled.</div></div>
<p><b>Pricing:</b> Free for unlimited sites. Pro starts at $99.99/year (1 site), $199.99/year (unlimited). 60-day money-back guarantee.</p>
<div class="ex-dashed"><b>Who it is for:</b> former Elementor users wanting better performance, agencies building WooCommerce stores, speed-conscious freelancers who want all-in-one features without managing addons.</div>

<hr>

<h2 id="performance">⚡ Performance deep dive: the numbers that matter</h2>
<p>Performance is where the four builders diverge most sharply. We tested each on an identical page — a typical business landing page with a hero section, three-column feature grid, testimonials, pricing table and contact form — on a shared hosting environment with no caching plugin.</p>
<div class="ex-card ex-card-neutral" style="margin:0;">
<div style="font-weight:700; margin-bottom:10px;">⚡ Time-to-Interactive (seconds, no cache)</div>
<svg viewBox="0 0 640 180" style="width:100%; height:auto;" role="img" aria-label="Time-to-Interactive comparison">
<rect x="96" y="8" width="112" height="22" rx="4" fill="#10b981"/><text x="216" y="23" font-size="12" font-weight="600" fill="#334155">1.4s</text>
<text x="88" y="23" font-size="12" text-anchor="end" fill="#475569">Bricks</text>
<rect x="96" y="42" width="160" height="22" rx="4" fill="#fb923c"/><text x="264" y="57" font-size="12" font-weight="600" fill="#334155">2.0s</text>
<text x="88" y="57" font-size="12" text-anchor="end" fill="#475569">Elementor V4</text>
<rect x="96" y="76" width="176" height="22" rx="4" fill="#06b6d4"/><text x="280" y="91" font-size="12" font-weight="600" fill="#334155">2.2s</text>
<text x="88" y="91" font-size="12" text-anchor="end" fill="#475569">Breakdance</text>
<rect x="96" y="110" width="224" height="22" rx="4" fill="#8b5cf6"/><text x="326" y="125" font-size="12" font-weight="600" fill="#334155">2.8s</text>
<text x="88" y="125" font-size="12" text-anchor="end" fill="#475569">Divi 5.0</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">VPS, no cache/CDN, identical test page. Lower is better.</div>
</div>
<div class="ex-card ex-card-neutral" style="margin-top:14px;">
<div style="font-weight:700; margin-bottom:10px;">📦 DOM elements (simple landing page)</div>
<svg viewBox="0 0 640 180" style="width:100%; height:auto;" role="img" aria-label="DOM elements comparison">
<rect x="96" y="8" width="38" height="22" rx="4" fill="#10b981"/><text x="144" y="23" font-size="12" font-weight="600" fill="#334155">75–100</text>
<text x="88" y="23" font-size="12" text-anchor="end" fill="#475569">Bricks</text>
<rect x="96" y="42" width="75" height="22" rx="4" fill="#fb923c"/><text x="181" y="57" font-size="12" font-weight="600" fill="#334155">~150</text>
<text x="88" y="57" font-size="12" text-anchor="end" fill="#475569">Breakdance</text>
<rect x="96" y="76" width="200" height="22" rx="4" fill="#06b6d4"/><text x="304" y="91" font-size="12" font-weight="600" fill="#334155">300–400</text>
<text x="88" y="91" font-size="12" text-anchor="end" fill="#475569">Elementor</text>
<rect x="96" y="110" width="400" height="22" rx="4" fill="#8b5cf6"/><text x="504" y="125" font-size="12" font-weight="600" fill="#334155">1,000–2,000</text>
<text x="88" y="125" font-size="12" text-anchor="end" fill="#475569">Divi</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">Fewer DOM elements = faster rendering. Bricks produces up to 20x fewer than Divi.</div>
</div>

<div class="ex-card ex-card-green"><b>💡 The performance takeaway:</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">If performance is your top priority, Bricks is the clear winner — cleanest code, fastest TTI, best CWV pass rate. Breakdance is a close second with significantly better ease of use. Elementor V4 is closing the gap but still heavier. Divi 5 improved from its predecessor but remains the heaviest of the four.</div></div>

<h3>Generated CSS weight (compressed)</h3>
<div class="ex-card ex-card-neutral">
<svg viewBox="0 0 640 160" style="width:100%; height:auto;" role="img" aria-label="CSS weight comparison">
<rect x="96" y="8" width="68" height="22" rx="4" fill="#10b981"/><text x="174" y="23" font-size="12" font-weight="600" fill="#334155">18 KB</text>
<text x="88" y="23" font-size="12" text-anchor="end" fill="#475569">Bricks</text>
<rect x="96" y="42" width="142" height="22" rx="4" fill="#fb923c"/><text x="248" y="57" font-size="12" font-weight="600" fill="#334155">38 KB</text>
<text x="88" y="57" font-size="12" text-anchor="end" fill="#475569">Breakdance</text>
<rect x="96" y="76" width="116" height="22" rx="4" fill="#06b6d4"/><text x="222" y="91" font-size="12" font-weight="600" fill="#334155">31 KB</text>
<text x="88" y="91" font-size="12" text-anchor="end" fill="#475569">Elementor V4</text>
<rect x="96" y="110" width="194" height="22" rx="4" fill="#8b5cf6"/><text x="300" y="125" font-size="12" font-weight="600" fill="#334155">52 KB</text>
<text x="88" y="125" font-size="12" text-anchor="end" fill="#475569">Divi</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">Compressed CSS output on identical test pages. Bricks generates 3x less CSS than Divi.</div>
</div>

<hr>

<h2 id="pricing">💰 Pricing: what you actually pay at year 1, 3 and 5</h2>
<p>The real cost of a page builder is not the first-year price — it is the total cost of ownership over the lifespan of your agency or site. Here is how the four builders compare:</p>
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">🔵 Elementor — $59–$399/year</div><div style="font-size:14px; line-height:1.6;">Essential $59/year (1 site), Advanced $99/year (3 sites), Expert $199/year (25 sites), Agency $399/year (1,000 sites). <b>Year 1:</b> $59–$399. <b>Year 3:</b> $177–$1,197. <b>Year 5:</b> $295–$1,995. No lifetime option. Renewals at full price.</div></div>
<div class="ex-card ex-card-violet"><div style="font-weight:700; margin-bottom:8px;">🟣 Divi — $89/year or $249 lifetime</div><div style="font-size:14px; line-height:1.6;">Yearly $89/year (unlimited sites). Lifetime $249 one-time (unlimited sites). <b>Year 1:</b> $89 or $249. <b>Year 3:</b> $267 or $249. <b>Year 5:</b> $445 or $249. <b>Lifetime wins after 3 years.</b> Divi Pro (AI + Cloud) at $277/year or $297 lifetime.</div></div>
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">🟢 Bricks — $79–$599</div><div style="font-size:14px; line-height:1.6;">Starter $79/year (1 site), Business $149/year (3 sites), Agency $249/year (unlimited), Lifetime $599 (unlimited). <b>Year 1:</b> $79–$599. <b>Year 3:</b> $237–$599. <b>Year 5:</b> $395–$599. <b>Lifetime wins for agencies using unlimited.</b></div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:8px;">🟠 Breakdance — $0–$799/year</div><div style="font-size:14px; line-height:1.6;">Free (80 elements, unlimited sites). Pro $99.99/year (1 site), $199.99/year (unlimited). No lifetime option. <b>Year 1:</b> $0–$199.99. <b>Year 3:</b> $0–$599.97. <b>Year 5:</b> $0–$999.95. Free tier is genuinely useful for simple sites.</div></div>

<div class="ex-card ex-card-neutral">
<div style="font-weight:700; margin-bottom:10px;">💸 5-year cost comparison (agency, unlimited sites)</div>
<svg viewBox="0 0 640 180" style="width:100%; height:auto;" role="img" aria-label="5-year cost comparison">
<rect x="96" y="8" width="60" height="22" rx="4" fill="#8b5cf6"/><text x="166" y="23" font-size="12" font-weight="600" fill="#334155">$249</text>
<text x="88" y="23" font-size="12" text-anchor="end" fill="#475569">Divi LT</text>
<rect x="96" y="42" width="140" height="22" rx="4" fill="#10b981"/><text x="246" y="57" font-size="12" font-weight="600" fill="#334155">$599</text>
<text x="88" y="57" font-size="12" text-anchor="end" fill="#475569">Bricks LT</text>
<rect x="96" y="76" width="260" height="22" rx="4" fill="#fb923c"/><text x="366" y="91" font-size="12" font-weight="600" fill="#334155">$999</text>
<text x="88" y="91" font-size="12" text-anchor="end" fill="#475569">Breakdance Pro</text>
<rect x="96" y="110" width="380" height="22" rx="4" fill="#06b6d4"/><text x="486" y="125" font-size="12" font-weight="600" fill="#334155">$1,995</text>
<text x="88" y="125" font-size="12" text-anchor="end" fill="#475569">Elementor Agency</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">5-year total cost for unlimited sites. Divi's lifetime deal is 4x cheaper than Elementor over 5 years.</div>
</div>

<hr>

<h2 id="scenarios">🧭 Which page builder should you choose? Five scenarios</h2>
<p>Scores are one thing; real-world fit is another. These are the five most common situations we see:</p>
<div class="ex-grid">
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">👋 "I am building my first WordPress site and want the easiest path."</div><div style="font-size:14px; line-height:1.6;">Start with <b>Elementor Free</b>. The largest tutorial library, the most intuitive interface, and a free version that covers most needs. Upgrade to Pro when you need Theme Builder or WooCommerce features.</div></div>
<div class="ex-card ex-card-violet"><div style="font-weight:700; margin-bottom:8px;">💼 "I am an agency building 20+ client sites per year."</div><div style="font-size:14px; line-height:1.6;">Choose <b>Divi Lifetime</b> ($249) for the lowest long-term cost, or <b>Bricks Agency</b> ($249/year) if performance and clean code are non-negotiable for your clients.</div></div>
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">⚡ "I care about Core Web Vitals and page speed above all else."</div><div style="font-size:14px; line-height:1.6;">Pick <b>Bricks</b>. Cleanest code, fastest TTI, best CWV pass rate. If you also want ease of use, <b>Breakdance</b> is a close second with a much gentler learning curve.</div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:8px;">🛒 "I am building a WooCommerce store and need deep integration."</div><div style="font-size:14px; line-height:1.6;">Evaluate <b>Breakdance</b> first — 37+ WooCommerce elements, cart/checkout builders and global WooCommerce styles are all built in. Bricks is the second-best WooCommerce builder.</div></div>
<div class="ex-card ex-card-neutral"><div style="font-weight:700; margin-bottom:8px;">💰 "I want the best free builder to start with no budget."</div><div style="font-size:14px; line-height:1.6;"><b>Breakdance Free</b> (80 elements, unlimited sites) or <b>Elementor Free</b> (57 widgets, unlimited sites). Breakdance Free produces cleaner output; Elementor Free has more tutorials available.</div></div>
</div>

<hr>

<h2 id="code">🔍 Code output: what your visitors' browsers actually receive</h2>
<p>The HTML each builder generates affects page speed, accessibility, and SEO. Here is how the four compare on a heading element:</p>
<div class="ex-card ex-card-neutral">
<div style="font-weight:700; margin-bottom:8px;">HTML output for a single H2 heading:</div>
<div class="ex-inner" style="font-family:monospace; font-size:13px; white-space:pre-wrap; line-height:1.5;">
<b>Bricks:</b>      &lt;h2 class="your-class"&gt;Heading&lt;/h2&gt;
<b>Breakdance:</b>  &lt;h2 class="your-class"&gt;Heading&lt;/h2&gt;
<b>Elementor:</b>   &lt;div class="elementor-widget-container"&gt;
                  &lt;div class="elementor-widget-heading"&gt;
                    &lt;h2 class="elementor-heading-title"&gt;Heading&lt;/h2&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
<b>Divi:</b>        &lt;div class="et_pb_module et_pb_text"&gt;
                  &lt;div class="et_pb_text_inner"&gt;
                    &lt;h2&gt;Heading&lt;/h2&gt;
                  &lt;/div&gt;
                &lt;/div&gt;</div>
</div>
<p>This pattern repeats for every element on the page. On a typical landing page, Elementor produces <b>5 nested DIVs per heading</b> and Divi produces <b>6</b>, while Bricks and Breakdance produce <b>1–2</b>. The cumulative effect is significant: more DOM nodes mean more memory, slower layout calculations, and worse INP (Interaction to Next Paint) scores.</p>
<div class="ex-dashed"><b>The practical impact:</b> a page built with Bricks typically passes Core Web Vitals on shared hosting without a caching plugin. The same page built with Elementor or Divi usually requires a caching plugin, CDN and careful optimization to achieve the same scores.</div>

<hr>

<h2 id="migration">🔄 Switching between page builders</h2>
<p>Migrating from one page builder to another is the elephant in the room. Here is the honest picture:</p>
<ul>
<li><b>Elementor → Bricks/Breakdance:</b> Breakdance has a built-in migration tool. For Bricks, manual recreation is typically required — Elementor's nested DIV structure does not map cleanly to Bricks' semantic output.</li>
<li><b>Divi → anything:</b> Divi 5.0 removed shortcodes, but legacy Divi sites still use them. Deactivating Divi leaves pages with unreadable bracketed text. Migration requires either staying on Divi 5 or rebuilding.</li>
<li><b>Oxygen → Breakdance:</b> Breakdance was built by the same team (Soflyy) specifically as a successor to Oxygen. Migration paths are documented and relatively smooth.</li>
<li><b>Any builder → Bricks:</b> Bricks does not have a built-in importer. Pages are typically rebuilt from scratch — which, given Bricks' lightweight output, often results in a faster site than any import could produce.</li>
</ul>
<div class="ex-card ex-card-orange"><b>⚠️ Honest warning:</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Page builder lock-in is real. Every builder stores content in its own format — Elementor uses JSON, Divi used shortcodes (now moving to blocks), Bricks uses block-based storage. Switching builders means rebuilding pages, not just deactivating a plugin. Choose carefully at the start.</div></div>

<hr>

<h2 id="faq">❓ Frequently asked questions</h2>
<div class="ex-card ex-card-neutral"><b>Do page builders slow down WordPress?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">It depends on the builder. Bricks and Breakdance produce output comparable to hand-coded themes — 42 KB baseline, 75–150 DOM elements. Elementor and Divi load significantly more assets (570 KB baseline, 300–2,000 DOM elements) and benefit from caching plugins and CDN. The performance gap between the lightest and heaviest builders is roughly 3x on identical pages.</div></div>
<div class="ex-card ex-card-neutral"><b>Which builder is best for WooCommerce?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Breakdance has the deepest WooCommerce integration: 37+ WooCommerce elements, visual cart/checkout/account builders, and global WooCommerce styles — all built in. Bricks is second with strong WooCommerce support and clean output. Elementor has 20+ WooCommerce widgets but requires Pro. Divi has WooCommerce modules but they are less extensive.</div></div>
<div class="ex-card ex-card-neutral"><b>Can I use multiple page builders on the same site?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Technically yes, but it is not recommended. Each builder loads its own CSS and JavaScript, so running two builders doubles the asset weight. Some users run Elementor for general pages and a lighter builder for performance-critical landing pages, but this adds complexity and maintenance overhead.</div></div>
<div class="ex-card ex-card-neutral"><b>Are page builders bad for SEO?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Not inherently. Google does not penalize page builders — it penalizes slow pages. Bricks and Breakdance produce output that scores 85–95 on mobile PageSpeed without optimization. Elementor and Divi can achieve good scores too, but typically require caching, CDN and careful widget selection. The SEO risk is performance, not the builder itself.</div></div>
<div class="ex-card ex-card-neutral"><b>What about Kadence, GenerateBlocks and Spectra?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">These are "block-based" builders that extend the native WordPress block editor (Gutenberg) rather than replacing it. They produce even lighter output than Bricks but offer fewer design capabilities. GenerateBlocks (9.7% of professionals) and Kadence (9.7%) are popular among developers who prefer staying within the block ecosystem. They are excellent choices for content-focused sites but cannot match the visual design freedom of Bricks, Elementor or Breakdance for landing pages and marketing sites.</div></div>
<div class="ex-card ex-card-neutral"><b>Should I wait for Elementor V4 or Divi 5 to mature?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Both V4 and 5.0 are significant improvements but still maturing. Elementor V4 is in alpha (opt-in) as of September 2026, with not all widgets migrated. Divi 5.0 is rolling out but frontend performance improvements are incremental. If you are starting a new project today, Bricks or Breakdance deliver their performance advantages right now — no waiting required.</div></div>

<h2 id="verdict">🔎 Final verdict</h2>
<div class="ex-summary-bg">
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:16px;">
<div class="ex-summary-card" style="border-left:4px solid #10b981;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🟢 Bricks — 9.1</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The performance king: cleanest code, fastest loads, best Core Web Vitals. Steep learning curve but highest professional satisfaction.</div><div class="ex-inner-green">Choose Bricks if performance is non-negotiable.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #06b6d4;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🔵 Elementor — 8.7</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The ecosystem king: largest community, most addons, easiest learning curve. Heavier output but V4 is closing the gap.</div><div class="ex-inner-cyan">Choose Elementor if you value breadth and beginner-friendliness.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #fb923c;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🟠 Breakdance — 8.5</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The balanced choice: performance-first output with an intuitive interface and all-in-one features.</div><div class="ex-inner-orange">Choose Breakdance if you want performance without the learning curve.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #8b5cf6;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🟣 Divi — 8.1</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The value play: $249 lifetime for unlimited sites, 200+ modules, built-in A/B testing. Weakest on performance.</div><div class="ex-inner-violet">Choose Divi if long-term cost is your top priority.</div></div>
</div>
</div>
<div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff; padding:20px; border-radius:14px; margin:0 0 14px;">
<div style="font-size:20px; font-weight:700; margin-bottom:12px; text-align:center;">⚡ The performance gap is real</div>
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:14px; margin-top:14px;">
<div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); padding:14px; border-radius:12px;"><div style="font-weight:700; margin-bottom:6px;">🟢 Bricks — 42 KB blank page</div><div style="font-size:14px; opacity:0.95;">1.4s TTI, 95 mobile PageSpeed, 75 DOM elements. No caching needed.</div></div>
<div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); padding:14px; border-radius:12px;"><div style="font-weight:700; margin-bottom:6px;">🟠 Breakdance — 42 KB blank page</div><div style="font-size:14px; opacity:0.95;">2.2s TTI, 90 mobile PageSpeed, ~150 DOM elements. Closest to Bricks.</div></div>
<div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); padding:14px; border-radius:12px;"><div style="font-weight:700; margin-bottom:6px;">🔵 Elementor — 570 KB blank page</div><div style="font-size:14px; opacity:0.95;">2.0s TTI (V4), 70–85 mobile PageSpeed, 300–400 DOM elements. Needs optimization.</div></div>
<div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); padding:14px; border-radius:12px;"><div style="font-weight:700; margin-bottom:6px;">🟣 Divi — 570 KB blank page</div><div style="font-size:14px; opacity:0.95;">2.8s TTI, 47–71 mobile PageSpeed, 1,000–2,000 DOM elements. Needs caching.</div></div>
</div>
</div>
<div class="ex-card ex-card-green-strong"><div style="font-weight:700;">The WordPress page builder market is no longer about "can I build a page visually?" — all four builders answer that with a yes. The real question is what happens after: how fast does the page load, how clean is the code, how much does it cost over five years, and how much of the ecosystem do you need access to. Bricks wins on performance. Elementor wins on ecosystem. Breakdance wins on balance. Divi wins on long-term cost. The right builder is the one whose trade-offs align with your priorities.</div></div>
<div style="background:#0f172a; color:#fff; padding:22px; border-radius:14px; text-align:center;">
<div style="font-size:24px; font-weight:700; margin-bottom:12px;">Your page speed is your first impression</div>
<div style="font-size:16px; line-height:1.6; opacity:0.95; max-width:720px; margin:0 auto;">
Every 100ms of load time costs you conversions. The page builder you choose determines how much optimization work you need to do before your site feels fast. Choose the one that starts fast — not the one that requires a caching plugin to catch up.
<div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.2);">Explore the tool cards above — every builder on this list has a full profile with criteria scores on this site.</div>
</div>
</div>
HTML;
}
