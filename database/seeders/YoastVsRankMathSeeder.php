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

class YoastVsRankMathSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::withTrashed()->where('slug->en', 'yoast-seo-vs-rank-math')->exists()) {
            $this->command->info('Article already exists — skipped to preserve admin edits. Delete the article to rebuild it from the seeder.');

            return;
        }

        $category = Category::query()->where('slug->en', 'seo-marketing')->first();

        foreach (['wordpress', 'seo', 'yoast', 'rank-math'] as $tagSlug) {
            Tag::query()->where('slug->en', $tagSlug)->first()
                ?? Tag::create(['slug' => ['en' => $tagSlug], 'name' => ['en' => str_replace('-', ' ', $tagSlug)]]);
        }

        $tools = [];
        $criteria = [];
        foreach (Criterion::query()->orderBy('sort_order')->get() as $criterion) {
            $criteria[$criterion->getTranslation('name', 'en')] = $criterion;
        }

        $toolData = [
            'Rank Math' => [
                'type' => ToolType::Plugin,
                'vendor' => 'Rank Math',
                'rating' => 8.5,
                'description' => 'The feature-packed WordPress SEO plugin: 4,000,000+ installs, a modular free version with redirects, a 404 monitor, local SEO, Google News and Video sitemaps, unlimited focus keywords, Google Search Console and GA4 integration built in, a schema generator with 840+ types on PRO, keyword rank tracking, and Content AI — PRO starts at €7.99/month billed annually for unlimited personal websites.',
                'affiliate' => false,
                'url' => 'https://rankmath.com/',
                'values' => ['Ease of use' => 9, 'Features' => 9, 'Performance' => 8, 'Pricing' => 8, 'Support & docs' => 8, 'Free plan' => true, 'Open source' => true],
            ],
            'Yoast SEO' => [
                'type' => ToolType::Plugin,
                'vendor' => 'Yoast',
                'rating' => 7.8,
                'description' => 'The most-installed WordPress SEO plugin (10,000,000+ sites since 2010) with the famous traffic-light content analysis, readability checks, inclusive language analysis and schema support. Since July 2025, Premium ($118.80/yr) bundles redirects, internal linking, AI title generation, crawl settings and the former Local, News and Video SEO add-ons in one subscription.',
                'affiliate' => false,
                'url' => 'https://yoast.com/wordpress/plugins/seo/',
                'values' => ['Ease of use' => 9, 'Features' => 8, 'Performance' => 7, 'Pricing' => 6, 'Support & docs' => 9, 'Free plan' => true, 'Open source' => true],
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
            } else {
                $tool->update([
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
                } else {
                    $tool->criteria()->updateExistingPivot($criterion->getKey(), ['value' => $value]);
                }
            }

            $link = $tool->links()->first();

            if ($link === null) {
                ToolLink::create([
                    'tool_id' => $tool->getKey(),
                    'code' => Str::slug($name),
                    'url' => $data['url'],
                    'anchor' => ['en' => 'Visit '.$name],
                    'is_affiliate' => $data['affiliate'],
                ]);
            } elseif (str_contains($link->url, 'example.com')) {
                $link->update([
                    'url' => $data['url'],
                    'anchor' => ['en' => 'Visit '.$name],
                ]);
            }

            $tools[$name] = $tool;
        }

        $article = Article::create([
            'category_id' => $category->getKey(),
            'title' => ['en' => 'Yoast SEO vs Rank Math (2026): The Honest WordPress SEO Plugin Comparison'],
            'slug' => ['en' => 'yoast-seo-vs-rank-math'],
            'excerpt' => ['en' => 'Yoast SEO and Rank Math are the two most popular WordPress SEO plugins. We compared free features, pricing over 3 years, content analysis, schema, technical SEO tools, support and ecosystem trust — to show which plugin fits which site.'],
            'body_html' => ['en' => self::ARTICLE_BODY],
            'cover' => 'uploads/article/apps/yoast-vs-rank-math/yoast/overview.png',
            'status' => ArticleStatus::Published->value,
            'published_at' => now(),
            'editor_mode' => EditorMode::Html->value,
            'meta_title' => ['en' => 'Yoast SEO vs Rank Math: Which WordPress SEO Plugin Wins in 2026?'],
            'meta_description' => ['en' => 'Plain-language comparison of Yoast SEO vs Rank Math: free features, pricing over 3 years, content analysis, schema and technical SEO tools — and which plugin fits your site.'],
        ]);

        $tagIds = Tag::query()->whereIn('slug->en', ['wordpress', 'seo', 'yoast', 'rank-math'])->pluck('id');
        $article->tags()->sync($tagIds);

        $comparison = $article->comparisons()->create([
            'title' => ['en' => 'Yoast SEO vs Rank Math: head-to-head'],
            'intro' => ['en' => 'Criteria scores from our testing methodology. Versions, installs and prices are current as of September 2026 (Yoast v28.4, Rank Math v1.0.278).'],
            'verdict' => ['en' => 'Rank Math gives away far more in the free version and its PRO covers unlimited personal websites from €7.99/month. Yoast wins on content analysis polish, documentation, SEO training and a 15-year track record. Pick Rank Math for feature value, Yoast for guided content quality.'],
            'sort_order' => 0,
        ]);

        $items = [
            ['Rank Math' => ['score' => 8.5, 'verdict' => 'Best feature value — redirects, 404 monitor, local SEO and unlimited focus keywords free; PRO from €7.99/mo for unlimited personal sites']],
            ['Yoast SEO' => ['score' => 7.8, 'verdict' => 'Best content guidance — the clearest traffic-light analysis, top documentation and SEO Academy; Premium bundles everything at $118.80/yr per site']],
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
<p style="margin:0 0 18px; font-size:17px;"><b>Yoast SEO</b> and <b>Rank Math</b> both do the same basic job: they tell Google what your pages mean and tell you how to improve them. But they come from opposite worlds. Yoast has been the standard since 2010 — installed on more than 10 million WordPress sites, famous for its green traffic-light analysis and its SEO academy. Rank Math appeared in 2018 and grew to 4 million sites by giving away, for free, the things Yoast charges for: redirects, a 404 monitor, local SEO and unlimited focus keywords. Both plugins hold a 4.8-star rating on WordPress.org. The real question is not which is "better" — it is which one matches the way you work.</p>
<div class="ex-nav-row">
<a href="#glance" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🔍 <b>At a glance</b><div class="ex-nav-sub">Facts + scores</div></div></a>
<a href="#setup" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">🧭 <b>Setup</b><div class="ex-nav-sub">First 15 minutes</div></div></a>
<a href="#content" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#fff7ed; border:1px solid #fed7aa;">✍️ <b>Content analysis</b><div class="ex-nav-sub">The core job</div></div></a>
<a href="#schema" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f5f3ff; border:1px solid #ddd6fe;">🖼️ <b>Preview + schema</b><div class="ex-nav-sub">How Google sees you</div></div></a>
<a href="#free" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🆓 <b>Free plan</b><div class="ex-nav-sub">What you get for $0</div></div></a>
<a href="#pricing" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">💰 <b>Pricing</b><div class="ex-nav-sub">3-year cost</div></div></a>
<a href="#technical" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#fff7ed; border:1px solid #fed7aa;">🛠️ <b>Technical SEO</b><div class="ex-nav-sub">Redirects, sitemaps, 404</div></div></a>
<a href="#trust" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f5f3ff; border:1px solid #ddd6fe;">🤝 <b>Support + trust</b><div class="ex-nav-sub">Who backs you up</div></div></a>
<a href="#scenarios" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🎯 <b>Which to choose</b><div class="ex-nav-sub">Four scenarios</div></div></a>
<a href="#faq" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">❓ <b>FAQ</b><div class="ex-nav-sub">Five honest answers</div></div></a>
</div>

<h2 id="glance">🔍 The two plugins at a glance</h2>
<div class="ex-grid">
<div class="ex-card ex-card-violet"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #ddd6fe;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>4.8 · 27,819 reviews · 10,000,000+ installs</div><b>Yoast SEO — the safe standard.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">Since 2010, version 28.4 (September 2026). The most-installed SEO plugin on WordPress, backed by a full-time team, its own SEO academy and documentation that has taught a generation of site owners. Strengths: content guidance, readability checks, trust. Weaknesses: the free version is lean and licenses are priced per site.</div></div>
<div class="ex-card ex-card-cyan"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #a5f3fc;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>4.8 · 7,501 reviews · 4,000,000+ installs</div><b>Rank Math — the generous challenger.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">Since 2018, version 1.0.278 (September 2026). Built as a modular "swiss army knife": you switch on the modules you need. Strengths: the most generous free plan, built-in Google Search Console and Analytics, keyword rank tracking. Weaknesses: more settings to learn, and the company is younger.</div></div>
</div>
<p>Both plugins are updated constantly — Yoast shipped version 28.4 on September 1, 2026, and Rank Math shipped 1.0.278 on September 8, 2026. Both work with the block editor, Elementor and Divi. And both now include an <b>llms.txt generator</b>, the new file that tells AI search engines what your site allows. The differences show up in what you get for free, how the paid plans are built, and how each plugin guides your writing.</p>
<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">📊 Our testing scores (weighted criteria, 10-point scale):</div>
<svg viewBox="0 0 760 268" style="width:100%; height:auto;" role="img" aria-label="Criteria scores: Yoast vs Rank Math">
<rect x="160" y="6" width="14" height="14" rx="3" fill="#7f54b3"/><text x="180" y="17" font-size="12" font-weight="600" fill="#334155">Yoast SEO</text>
<rect x="280" y="6" width="14" height="14" rx="3" fill="#06b6d4"/><text x="300" y="17" font-size="12" font-weight="600" fill="#334155">Rank Math</text>
<text x="152" y="53" font-size="12" text-anchor="end" fill="#475569">Ease of use</text>
<rect x="160" y="36" width="234" height="16" rx="4" fill="#7f54b3"/><text x="402" y="49" font-size="12" font-weight="700" fill="#334155">9</text>
<rect x="160" y="56" width="234" height="16" rx="4" fill="#06b6d4"/><text x="402" y="69" font-size="12" font-weight="700" fill="#334155">9</text>
<text x="152" y="99" font-size="12" text-anchor="end" fill="#475569">Features</text>
<rect x="160" y="82" width="208" height="16" rx="4" fill="#7f54b3"/><text x="376" y="95" font-size="12" font-weight="700" fill="#334155">8</text>
<rect x="160" y="102" width="234" height="16" rx="4" fill="#06b6d4"/><text x="402" y="115" font-size="12" font-weight="700" fill="#334155">9</text>
<text x="152" y="145" font-size="12" text-anchor="end" fill="#475569">Performance</text>
<rect x="160" y="128" width="182" height="16" rx="4" fill="#7f54b3"/><text x="350" y="141" font-size="12" font-weight="700" fill="#334155">7</text>
<rect x="160" y="148" width="208" height="16" rx="4" fill="#06b6d4"/><text x="376" y="161" font-size="12" font-weight="700" fill="#334155">8</text>
<text x="152" y="191" font-size="12" text-anchor="end" fill="#475569">Pricing</text>
<rect x="160" y="174" width="156" height="16" rx="4" fill="#7f54b3"/><text x="324" y="187" font-size="12" font-weight="700" fill="#334155">6</text>
<rect x="160" y="194" width="208" height="16" rx="4" fill="#06b6d4"/><text x="376" y="207" font-size="12" font-weight="700" fill="#334155">8</text>
<text x="152" y="237" font-size="12" text-anchor="end" fill="#475569">Support &amp; docs</text>
<rect x="160" y="220" width="234" height="16" rx="4" fill="#7f54b3"/><text x="402" y="233" font-size="12" font-weight="700" fill="#334155">9</text>
<rect x="160" y="240" width="208" height="16" rx="4" fill="#06b6d4"/><text x="376" y="253" font-size="12" font-weight="700" fill="#334155">8</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">Scores from our standard testing methodology: features and ease of use count three times, performance and pricing twice, support once. Weighted totals: Rank Math 8.5, Yoast 7.8.</div>
</div>

<h2 id="comparison">⚖️ The head-to-head table</h2>
<p>The full scorecard is below — scores, prices and a one-line verdict for each plugin. Every row is unpacked in the sections that follow.</p>
[[comparison:COMPARISON_ID]]
<div class="ex-dashed"><b>How to read the table:</b> each score mixes several criteria — features and ease of use matter most, then speed and price, then support. "Free plan" and "Open source" are marked separately. Data was checked in September 2026.</div>

<h2 id="setup">🧭 Setup: the first 15 minutes</h2>
<p><b>Yoast</b> walks you through a first-time configuration: it asks whether your site is a business or a personal blog, whether you are a company or a person, and fills in the basics for Google. The steps are short and the defaults are sensible — most sites are done in ten minutes and never touch the settings again.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/yoast/setup.png" alt="Yoast SEO first-time configuration walkthrough" loading="lazy"><figcaption>Yoast's first-time configuration: a short guided interview that sets the basics for you. Screenshot: Yoast.</figcaption></figure>
</div>
<p><b>Rank Math</b> runs a proper setup wizard with a compatibility check and pre-selected optimal settings — a longer process, but it finishes with more configured: sitemaps on, Search Console connected, analytics wired up. Its killer trick is the <b>one-click importer</b>: if you already run Yoast, All in One SEO or SEOPress, Rank Math copies over your titles, meta descriptions and settings, so switching does not mean redoing everything.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/rank-math/setup-wizard.gif" alt="Rank Math setup wizard with 1-click configuration" loading="lazy"><figcaption>Rank Math's setup wizard with 1-click configuration — including import from Yoast. Screenshot: Rank Math.</figcaption></figure>
</div>
<div class="ex-dashed"><b>The difference in one sentence:</b> Yoast gets you started faster; Rank Math gets you started deeper — and imports your Yoast data if you are switching.</div>

<h2 id="content">✍️ Content analysis: the core job</h2>
<p>This is where both plugins earn their keep, and where they feel completely different.</p>
<p><b>Yoast's traffic-light analysis</b> is still the best writing coach in WordPress. You type a focus keyword, and the plugin grades your post: green for good, orange for improvable, red for a problem. It checks whether your keyword is in the title, the first paragraph and the headings; whether sentences are too long; whether paragraphs are readable; whether you used transition words. It even checks <b>inclusive language</b> — flagging wording that may read as non-inclusive, a feature no competitor matches. The analysis has real teeth: it is written by linguists and SEO engineers, and it teaches you to write better over time.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/yoast/content-analysis.png" alt="Yoast SEO traffic-light content and readability analysis" loading="lazy"><figcaption>The famous Yoast analysis: SEO and readability checks side by side while you write. Screenshot: Yoast.</figcaption></figure>
</div>
<p><b>Rank Math</b> takes a different approach: instead of one keyword, you can add <b>unlimited focus keywords</b> — even on the free plan — and the plugin scores your post 0–100 against more than 30 tests. The checklist style shows exactly which tests pass and fail. Where Rank Math pulls ahead is AI: its <b>Content AI</b> module suggests keywords, writes outlines and drafts, and scores your content against competitors — though it works on a credit system and is a separate subscription beyond the 15-day trial included with paid plans.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/rank-math/content-ai.gif" alt="Rank Math Content AI assistant generating and optimizing content" loading="lazy"><figcaption>Content AI: Rank Math's assistant for outlines, drafts and SEO-scored content. Screenshot: Rank Math.</figcaption></figure>
</div>
<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">🔑 How many keywords can you optimize per post?</div>
<svg viewBox="0 0 760 118" style="width:100%; height:auto;" role="img" aria-label="Focus keywords per post">
<text x="168" y="34" font-size="12" text-anchor="end" fill="#475569">Yoast Free</text>
<rect x="175" y="20" width="18" height="22" rx="4" fill="#7f54b3"/><text x="201" y="36" font-size="12" font-weight="700" fill="#334155">1 keyword</text>
<text x="168" y="70" font-size="12" text-anchor="end" fill="#475569">Yoast Premium</text>
<rect x="175" y="56" width="90" height="22" rx="4" fill="#9f7aea"/><text x="273" y="72" font-size="12" font-weight="700" fill="#334155">5 keywords</text>
<text x="168" y="106" font-size="12" text-anchor="end" fill="#475569">Rank Math Free</text>
<rect x="175" y="92" width="300" height="22" rx="4" fill="#06b6d4"/><text x="483" y="108" font-size="12" font-weight="700" fill="#334155">Unlimited</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">Rank Math PRO keeps keywords unlimited and adds related keywords from Google's own suggest data. Yoast Premium raises the limit to 5 per post; extra packs cost more.</div>
</div>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/yoast/inclusive-language.png" alt="Yoast inclusive language analysis in the WordPress editor" loading="lazy"><figcaption>Yoast's inclusive language analysis — unique among SEO plugins. Screenshot: Yoast.</figcaption></figure>
</div>

<h2 id="schema">🖼️ Google preview and schema</h2>
<p>Both plugins show how your page will look in Google before you publish: the title, the web address and the description. <b>Yoast's snippet preview</b> is the cleaner one — it renders your result as a real Google listing, mobile and desktop side by side, and warns you when the title will be cut off.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/yoast/google-preview.png" alt="Yoast SEO Google search preview" loading="lazy"><figcaption>Yoast shows your Google listing before you publish. Screenshot: Yoast.</figcaption></figure>
</div>
<p><b>Schemas</b> — the hidden labels that win star ratings, recipe cards and FAQ boxes in search results — are another story. Yoast sets up solid default schema for articles and organizations automatically, and you can add FAQ and HowTo blocks by hand. <b>Rank Math</b> turns schema into a playground: 18 built-in types on the free plan (articles, recipes, events, jobs, products and more), automatic FAQ schema from page builders, and a visual schema builder with <b>840+ types</b> on paid plans — you can even copy schema from any competitor's page and validate it against Google.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/rank-math/rich-snippets.gif" alt="Rank Math rich snippet schema types" loading="lazy"><figcaption>Rank Math's schema picker: 18+ types free, 840+ on paid plans. Screenshot: Rank Math.</figcaption></figure>
</div>
<div class="ex-dashed"><b>Plain-language summary:</b> Yoast gives you correct schema without thinking about it. Rank Math gives you any schema you can imagine — if you are willing to configure it.</div>

<h2 id="free">🆓 What you get for $0</h2>
<p>This is the sharpest difference between the two plugins. Yoast's free version covers the writing essentials and stops there. Rank Math's free version includes whole modules that Yoast sells in Premium. Here is the honest checklist:</p>
<div style="overflow-x:auto;">
<table style="width:100%; border-collapse:collapse; font-size:14px; margin:0 0 16px;">
<thead><tr><th style="text-align:left; padding:8px 10px; border-bottom:2px solid #cbd5e1; font-size:13px; color:#475569;">Feature</th><th style="text-align:center; padding:8px 10px; border-bottom:2px solid #cbd5e1; font-size:13px; color:#7f54b3;">Yoast Free</th><th style="text-align:center; padding:8px 10px; border-bottom:2px solid #cbd5e1; font-size:13px; color:#0891b2;">Rank Math Free</th></tr></thead>
<tbody>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Content + readability analysis</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Google preview (search snippet)</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">XML sitemaps</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">llms.txt generator for AI crawlers</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Focus keywords per post</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">1</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">Unlimited</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Redirect manager</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">❌ Premium</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">404 monitor</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">❌ Premium</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Local SEO (structured data)</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">❌ Premium</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Google News sitemap</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">❌ Premium</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Video SEO sitemap</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">❌ Premium</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Role manager (who sees what)</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">❌ Premium</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Google Search Console + GA4 inside WordPress</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">❌ (use Site Kit)</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Internal linking suggestions</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">❌ Premium</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">✅ (basic)</td></tr>
<tr><td style="padding:8px 10px; border-bottom:1px solid #e2e8f0;">Built-in schema types</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">Basics</td><td style="text-align:center; border-bottom:1px solid #e2e8f0;">18+</td></tr>
</tbody>
</table>
</div>
<p>The free-plan logic matters most for bloggers and small sites: with Rank Math you may never need to pay at all. Yoast's free version is honest but minimal — it assumes the Premium upgrade is coming.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/yoast/indexation.png" alt="Yoast SEO indexation controls for posts and pages" loading="lazy"><figcaption>Yoast's indexation controls: decide what Google may see, per content type. Screenshot: Yoast.</figcaption></figure>
</div>

<h2 id="pricing">💰 Pricing: what you pay, and what it costs over 3 years</h2>
<p>Yoast restructured its whole price list in July 2025. The old maze of ten add-ons is gone; there are now two subscriptions. <b>Yoast SEO Premium ($118.80/yr per site)</b> bundles everything the old add-ons did — Local SEO, News SEO, Video SEO — plus the redirect manager, internal linking, AI title generation, crawl settings and a Google Docs add-on seat. <b>Yoast WooCommerce SEO ($178.80/yr)</b> adds product-specific analysis and schema on top. The catch: every license covers <b>one site</b>.</p>
<p>Rank Math prices per account, not per site. <b>PRO (€7.99/month, billed annually — renews at €8.99)</b> covers <b>unlimited personal websites</b>, adds the rank tracker for 500 keywords, Google News and Video sitemaps pro features, image SEO and white-label-free reports. <b>Business (€24.99/mo)</b> covers 100 client sites with 10,000 tracked keywords; <b>Agency (€54.99/mo)</b> covers 500 sites and 50,000 keywords. All plans include a 30-day money-back guarantee, and Content AI remains a separate credit-based product with a free trial.</p>
<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">💸 Total cost of paid plans over 3 years:</div>
<svg viewBox="0 0 760 136" style="width:100%; height:auto;" role="img" aria-label="3-year cost of paid plans">
<text x="168" y="32" font-size="12" text-anchor="end" fill="#475569">Rank Math PRO</text>
<rect x="175" y="16" width="207" height="24" rx="4" fill="#06b6d4"/><text x="390" y="32" font-size="12" font-weight="700" fill="#334155">$337 (€312 — promo year + 2 renewals, unlimited personal sites)</text>
<text x="168" y="70" font-size="12" text-anchor="end" fill="#475569">Yoast Premium</text>
<rect x="175" y="54" width="219" height="24" rx="4" fill="#7f54b3"/><text x="402" y="70" font-size="12" font-weight="700" fill="#334155">$356 ($118.80 × 3, one site)</text>
<text x="168" y="108" font-size="12" text-anchor="end" fill="#475569">Yoast Woo SEO</text>
<rect x="175" y="92" width="330" height="24" rx="4" fill="#5b21b6"/><text x="513" y="108" font-size="12" font-weight="700" fill="#fff">$536 ($178.80 × 3, one site)</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">List prices, September 2026, ex VAT. Rank Math shows prices in your local currency; here converted at ≈1.08 USD/EUR. Yoast prices already reflect the July 2025 bundle restructure (Premium now includes Local, News and Video SEO — old buyers of single add-ons saw prices rise to $118.80).</div>
</div>
<div class="ex-dashed"><b>The pricing verdict:</b> for one site the two cost almost the same over 3 years ($337 vs $356). For two or more own sites, Rank Math wins outright — one PRO license covers them all, while Yoast charges per site. Agencies get volume pricing from Rank Math and custom quotes from Yoast.</div>

<h2 id="technical">🛠️ Technical SEO: redirects, sitemaps, 404s</h2>
<p>Both plugins generate XML sitemaps, manage breadcrumbs, edit robots.txt and ship the new llms.txt file for AI crawlers. The differences sit in the plumbing.</p>
<p><b>Redirects:</b> Yoast's redirect manager is Premium-only, but it is the smarter one — it suggests redirects automatically when you change a URL or delete a post. Rank Math's redirect manager is free and covers 301, 302, 307, 410 and regex rules, syncing to .htaccess on paid plans. <b>404 monitoring:</b> Rank Math watches broken links and logs dead pages for free; Yoast has no equivalent in the free version.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/rank-math/404-monitor.gif" alt="Rank Math 404 monitor logging broken links" loading="lazy"><figcaption>Rank Math's 404 monitor: every dead link, logged and redirect-ready — free. Screenshot: Rank Math.</figcaption></figure>
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/rank-math/sitemap.jpg" alt="Rank Math XML sitemap configuration" loading="lazy"><figcaption>Rank Math's XML sitemap module: automatic, search-engine friendly. Screenshot: Rank Math.</figcaption></figure>
</div>
<p><b>Crawl cleanup:</b> Yoast Premium can strip the RSS feeds, date archives and pagination clutter that waste Google's attention on big sites — a feature Rank Math answers with its robots.txt editor and noindex controls. <b>Performance plumbing:</b> both keep the public-facing site light (they output meta tags and schema; nothing heavy loads for visitors), but Rank Math's modular design lets you switch off anything you do not use, while Yoast loads as one integrated plugin.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/yoast-vs-rank-math/yoast/crawl-settings.png" alt="Yoast SEO Premium crawl optimization settings" loading="lazy"><figcaption>Yoast Premium's crawl settings: remove the clutter Google does not need. Screenshot: Yoast.</figcaption></figure>
</div>

<h2 id="trust">🤝 Support, docs and trust</h2>
<p><b>Yoast</b> has the deepest safety net: 24/7 email support for paid users, developer documentation on a dedicated site, and the Yoast SEO Academy — full video courses that teach SEO from scratch, free with Premium. The plugin has 15 years of stability, 10 million installs and one of the largest review bases on WordPress.org (27,819 reviews, 4.8 stars). Yoast is owned by Newfold Digital, the company behind Web.com and Bluehost — big-company resources, big-company pace.</p>
<p><b>Rank Math</b> offers 24/7 support from the PRO plan up, a large knowledge base and fast plugin updates — it shipped its latest release a week before Yoast's at the time of writing. Its support threads on WordPress.org resolve at 89%, a whisker above Yoast's 88%. The honest caution flag: Rank Math had one serious security incident in December 2023, a stored XSS hole exploited in the wild, patched within days in version 3.0.94. Since then its record has been clean — but Yoast has never had an incident of that scale, and its 27,819 reviews versus Rank Math's 7,501 reflect a much longer runway of public trust.</p>
<div class="ex-grid">
<div class="ex-card ex-card-violet"><b>Yoast's trust column</b><div style="margin-top:8px; font-size:14px; line-height:1.8;">
✅ 15 years, 10M+ installs<br>
✅ 27,819 public reviews at 4.8★<br>
✅ SEO Academy + best-in-class docs<br>
✅ No major security incidents<br>
⚠️ Per-site pricing, Newfold ownership
</div></div>
<div class="ex-card ex-card-cyan"><b>Rank Math's trust column</b><div style="margin-top:8px; font-size:14px; line-height:1.8;">
✅ 8 years, 4M+ installs<br>
✅ 7,501 public reviews at 4.8★<br>
✅ Fast updates, 89% resolved support<br>
✅ 30-day money-back on all plans<br>
⚠️ One exploited XSS incident (Dec 2023, patched)
</div></div>
</div>

<h2 id="scenarios">🎯 Which plugin should you pick? Four scenarios</h2>
<div class="ex-grid">
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">💰 "I run one or several blogs on a budget."</div><div style="font-size:14px; line-height:1.6;">Take <b>Rank Math</b>. The free version already includes redirects, the 404 monitor, local SEO and unlimited focus keywords. PRO later — €7.99/month covering all your personal sites — costs less than one Yoast Premium license.</div></div>
<div class="ex-card ex-card-violet"><div style="font-weight:700; margin-bottom:8px;">✍️ "I write for a living and want the best writing coach."</div><div style="font-size:14px; line-height:1.6;">Take <b>Yoast</b>. The traffic-light analysis, readability checks and inclusive language feedback are the closest thing to an editor looking over your shoulder. Premium adds AI titles and the Google Docs workflow.</div></div>
<div class="ex-card ex-card-neutral"><div style="font-weight:700; margin-bottom:8px;">🏢 "I manage client sites as an agency."</div><div style="font-size:14px; line-height:1.6;">Take <b>Rank Math Business</b> (100 client sites, €24.99/mo) or Agency (500 sites). Yoast's custom quotes rarely compete at that scale, and Rank Math's client management plus white-label reports are built for the job.</div></div>
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">🛒 "I run a WooCommerce store."</div><div style="font-size:14px; line-height:1.6;">Both are strong: Rank Math includes WooCommerce SEO free with product schema; Yoast charges $178.80/yr for its WooCommerce package. If you want free, take Rank Math; if you want Yoast's guided analysis on product pages, budget for its Woo plan.</div></div>
</div>

<h2 id="faq">❓ Frequently asked questions</h2>
<div class="ex-card ex-card-neutral"><b>Can I switch from Yoast to Rank Math without losing my settings?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Yes — this is Rank Math's party trick. Its setup wizard imports your Yoast titles, meta descriptions and settings in one click, and deactivating Yoast afterward does not remove your content. Keep backups as always, and spot-check a few pages after the switch.</div></div>
<div class="ex-card ex-card-neutral"><b>Can I run both plugins at the same time?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Technically they can both be installed, but you should never run two SEO plugins at once — you would get duplicated sitemaps, duplicated schema and conflicting meta tags. Pick one. If you want to try the other, export or import settings first, then deactivate the old plugin.</div></div>
<div class="ex-card ex-card-neutral"><b>Is Yoast Premium still worth it after the 2025 price changes?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">For a single content-focused site, yes — $118.80/yr now includes what used to cost $258+ (Premium + Local + News + Video), plus AI features and the redirect manager. But remember the license covers one site, and the free version plus a dedicated redirect plugin can cover simpler needs at $0.</div></div>
<div class="ex-card ex-card-neutral"><b>Do these plugins slow down my site?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Not for visitors: both output meta tags and schema code and load almost nothing on the public site. The weight sits in the admin area — Rank Math's analytics queries can feel heavy on very large sites or weak shared hosting, and Yoast's analysis runs in the editor on every post. Both are fine on normal hosting; both let you trim what you do not use.</div></div>
<div class="ex-card ex-card-neutral"><b>Which plugin is better for AI search and llms.txt?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Both ship an llms.txt generator now — Yoast added one in June 2026 and Rank Math has a more advanced generator with editable content. Rank Math additionally tracks AI search traffic on paid plans. Neither is a magic bullet for AI visibility: clear content and proper schema still do the heavy lifting.</div></div>

<h2 id="verdict">🔎 Final verdict</h2>
<div class="ex-summary-bg">
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:16px;">
<div class="ex-summary-card" style="border-left:4px solid #06b6d4;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🔄 Rank Math — 8.5</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The value winner: redirects, 404 monitoring, local SEO, unlimited keywords and Google integration free. PRO from €7.99/mo covers unlimited personal sites with rank tracking and 840+ schema types.</div><div class="ex-inner-cyan">Pick it for feature value and multi-site savings.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #7f54b3;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🏆 Yoast SEO — 7.8</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The trusted standard: the best content analysis, inclusive language checks, top documentation and the SEO Academy. Premium now bundles everything at $118.80/yr per site.</div><div class="ex-inner-violet">Pick it for guided content quality and maximum trust.</div></div>
</div>
</div>
<div class="ex-card ex-card-green-strong"><div style="font-weight:700;">In short: <b>Rank Math</b> gives you more for less — it is the rational default for bloggers, multi-site owners and agencies. <b>Yoast</b> is the choice when content guidance, polish and a 15-year safety net matter more than the feature count. And the good news: switching between them is one click, so you can always change your mind.</div></div>
<style>
.ex-cta-deep{position:relative;overflow:hidden;border-radius:16px;padding:28px 22px;text-align:center;color:#fff;background:radial-gradient(600px 220px at 15% 0%,rgba(124,58,237,.55),transparent 60%),radial-gradient(600px 240px at 85% 100%,rgba(8,145,178,.5),transparent 60%),linear-gradient(135deg,#111827,#1e1b4b 55%,#111827);box-shadow:0 12px 32px rgba(17,24,39,.3)}
.ex-cta-deep>*{position:relative;z-index:1}
</style>
<div class="ex-cta-deep">
<div style="font-size:24px; font-weight:700; margin-bottom:12px;">Good SEO is 90% habits, 10% plugins</div>
<div style="font-size:16px; line-height:1.6; opacity:0.95; max-width:720px; margin:0 auto;">
Both of these plugins will win you rankings only if you keep publishing, keep fixing what they flag, and keep your pages fast. Pick the one whose advice you will actually follow — that is the only comparison that matters in a year.
<div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.25);">Explore the tool cards above — both plugins have full profiles with criteria scores on this site.</div>
</div>
</div>
HTML;
}
