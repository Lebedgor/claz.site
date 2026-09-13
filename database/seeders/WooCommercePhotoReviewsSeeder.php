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

class WooCommercePhotoReviewsSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::withTrashed()->where('slug->en', 'best-woocommerce-photo-review-plugins')->exists()) {
            $this->command->info('Article already exists — skipped to preserve admin edits. Delete the article to rebuild it from the seeder.');

            return;
        }

        $category = Category::query()->where('slug->en', 'woocommerce-plugins')->first()
            ?? Category::create([
                'name' => ['en' => 'WooCommerce plugins'],
                'slug' => ['en' => 'woocommerce-plugins'],
                'description' => ['en' => 'Honest reviews and comparisons of plugins that make WooCommerce stores sell more.'],
                'sort_order' => 35,
            ]);

        foreach (['woocommerce', 'product-reviews', 'ecommerce', 'photo-reviews'] as $tagSlug) {
            Tag::query()->where('slug->en', $tagSlug)->first()
                ?? Tag::create(['slug' => ['en' => $tagSlug], 'name' => ['en' => str_replace('-', ' ', $tagSlug)]]);
        }

        $tools = [];
        $criteria = [];
        foreach (Criterion::query()->orderBy('sort_order')->get() as $criterion) {
            $criteria[$criterion->getTranslation('name', 'en')] = $criterion;
        }

        $toolData = [
            'PVR Media Reviews' => [
                'type' => ToolType::Plugin,
                'vendor' => 'PVR',
                'rating' => 9.5,
                'description' => 'A self-hosted photo and video review plugin that compresses videos in the customer browser before upload — no FFmpeg, no server CPU load. Native WooCommerce review storage, built-in rich snippets, helpful voting, store replies, an SEO-first Pro tier with a reviews hub page, and a founding-customer lifetime license at $59.',
                'affiliate' => false,
                'url' => 'https://wordpress.org/plugins/pvr-media-reviews-for-woocommerce/',
                'values' => ['Ease of use' => 9, 'Features' => 10, 'Performance' => 10, 'Pricing' => 9, 'Support & docs' => 7, 'Free plan' => true, 'Open source' => true],
            ],
            'Photo Reviews for WooCommerce' => [
                'type' => ToolType::Plugin,
                'vendor' => 'Xyno Tech',
                'rating' => 8.6,
                'description' => 'The official WooCommerce Marketplace photo review plugin: built-in image cropping, automated review request emails, coupon incentives for photo submissions, verified purchase badges, five display layouts, rating filters, helpful votes, and full admin control — all for $29/year with a 30-day money-back guarantee.',
                'affiliate' => false,
                'url' => 'https://woocommerce.com/products/photo-reviews',
                'values' => ['Ease of use' => 9, 'Features' => 8, 'Performance' => 8, 'Pricing' => 9, 'Support & docs' => 8, 'Free plan' => false, 'Open source' => false],
            ],
            'Customer Reviews for WooCommerce' => [
                'type' => ToolType::Plugin,
                'vendor' => 'CusRev',
                'rating' => 8.8,
                'description' => 'The most-installed WooCommerce review plugin (80,000+ stores): free forever with unlimited review reminders, photo and video reviews, discount coupons, trust badges, Google Shopping feeds, rich snippets, Q&A, and CSV import/export — Pro adds branding and a visual email editor.',
                'affiliate' => false,
                'url' => 'https://wordpress.org/plugins/customer-reviews-woocommerce/',
                'values' => ['Ease of use' => 9, 'Features' => 8, 'Performance' => 8, 'Pricing' => 9, 'Support & docs' => 7, 'Free plan' => true, 'Open source' => true],
            ],
            'ReviewX' => [
                'type' => ToolType::Plugin,
                'vendor' => 'ReviewX',
                'rating' => 7.2,
                'description' => 'Multi-criteria photo and video review plugin: customers rate products on individual attributes (quality, fit, value), upload photos with reviews, and get verified badges — but a high-severity stored XSS vulnerability (CVE-2026-57359) was disclosed in 2026 and must be patched.',
                'affiliate' => false,
                'url' => 'https://wordpress.org/plugins/reviewx/',
                'values' => ['Ease of use' => 7, 'Features' => 8, 'Performance' => 7, 'Pricing' => 7, 'Support & docs' => 7, 'Free plan' => true, 'Open source' => true],
            ],
            'Yuko' => [
                'type' => ToolType::Plugin,
                'vendor' => 'Yuko Apps',
                'rating' => 8.0,
                'description' => 'The Judge.me replacement for WooCommerce: one-click import from Judge.me, automated review requests with configurable delays, photo reviews on mobile-first forms, 8 display widgets, Google rich snippets, multi-site syndication, and a free tier for stores up to 50 orders/month.',
                'affiliate' => false,
                'url' => 'https://wordpress.org/plugins/yuko-integration/',
                'values' => ['Ease of use' => 9, 'Features' => 8, 'Performance' => 8, 'Pricing' => 8, 'Support & docs' => 8, 'Free plan' => true, 'Open source' => false],
            ],
            'Photo Reviews for WooCommerce by VillaTheme' => [
                'type' => ToolType::Plugin,
                'vendor' => 'VillaTheme',
                'rating' => 7.8,
                'description' => 'The free WordPress.org photo review plugin with 10,000+ installs: customer photo uploads, review reminder emails, auto-generated coupons for reviews, GDPR compliance, review filters, and a grid or default front-end layout — the premium adds AliExpress import and import/export via CSV.',
                'affiliate' => false,
                'url' => 'https://wordpress.org/plugins/woo-photo-reviews/',
                'values' => ['Ease of use' => 8, 'Features' => 7, 'Performance' => 7, 'Pricing' => 9, 'Support & docs' => 7, 'Free plan' => true, 'Open source' => true],
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
            'title' => ['en' => 'Best WooCommerce Review Plugins for Photo Reviews (2026): PVR Media Reviews, CusRev, VillaTheme, Yuko, ReviewX and the Official WooCommerce Plugin'],
            'slug' => ['en' => 'best-woocommerce-photo-review-plugins'],
            'excerpt' => ['en' => 'We compared six WooCommerce photo review plugins — PVR Media Reviews, the official Marketplace plugin, VillaTheme free, Customer Reviews for WooCommerce, ReviewX and Yuko — on pricing, photo collection, display, SEO and data ownership to find the right visual review tool for every store.'],
            'body_html' => ['en' => self::ARTICLE_BODY],
            'cover' => 'uploads/article/apps/customer-reviews/banner-1544x500.jpg',
            'status' => ArticleStatus::Published->value,
            'published_at' => now(),
            'editor_mode' => EditorMode::Html->value,
            'meta_title' => ['en' => 'Best WooCommerce Photo Review Plugins Compared (2026)'],
            'meta_description' => ['en' => 'Plain-language comparison of the six best WooCommerce photo review plugins: prices over 3 years, photo collection, display and which plugin fits your store.'],
        ]);

        $tagIds = Tag::query()->whereIn('slug->en', ['woocommerce', 'product-reviews', 'ecommerce', 'photo-reviews'])->pluck('id');
        $article->tags()->sync($tagIds);

        $comparison = $article->comparisons()->create([
            'title' => ['en' => 'WooCommerce photo review plugins: head-to-head'],
            'intro' => ['en' => 'Criteria scores from our testing methodology. Prices, plans and install counts are current as of September 2026.'],
            'verdict' => ['en' => 'PVR Media Reviews is the best long-term deal ($59 once); Customer Reviews for WooCommerce is the best free option; the official Marketplace plugin is the focused $29/year photo specialist; Yuko fits Judge.me refugees; VillaTheme is the budget pick; ReviewX suits stores that want detailed multi-criteria ratings.'],
            'sort_order' => 0,
        ]);

        $items = [
            ['PVR Media Reviews' => ['score' => 9.5, 'verdict' => 'Best overall — photos and videos on your own site, a reviews hub for Google, $59 once']],
            ['Customer Reviews for WooCommerce' => ['score' => 8.8, 'verdict' => 'Best free option — photo reviews, coupons and Google Shopping feeds cost nothing']],
            ['Photo Reviews for WooCommerce' => ['score' => 8.6, 'verdict' => 'Best dedicated photo tool — cropping, five layouts, $29/yr']],
            ['Yuko' => ['score' => 8.0, 'verdict' => 'Best Judge.me replacement — one-click import, phone-first forms, 8 widgets']],
            ['Photo Reviews for WooCommerce by VillaTheme' => ['score' => 7.8, 'verdict' => 'Cheapest start — photo reviews free, premium about $32 once']],
            ['ReviewX' => ['score' => 7.2, 'verdict' => 'Detailed ratings for quality, fit and value — photos need Pro; update to 2.3.11+ first']],
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
<p style="margin:0 0 18px; font-size:17px;"><b>Photo reviews</b> — reviews with a real customer photo — sell better than plain text reviews. Research from Northwestern University found that products with customer photos get bought noticeably more often than products with text reviews alone. The problem: WooCommerce cannot ask for photos, reward customers for them or show them nicely on its own. You need a plugin. In this guide we compare six of them in plain language — with prices and honest downsides for each.</p>
<div class="ex-nav-row">
<a href="#why-photo" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">📸 <b>Why photo reviews</b><div class="ex-nav-sub">Why they sell</div></div></a>
<a href="#comparison" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#fff7ed; border:1px solid #fed7aa;">⚖️ <b>At a glance</b><div class="ex-nav-sub">Scores + prices</div></div></a>
<a href="#pvr" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">📸 <b>PVR</b><div class="ex-nav-sub">Photos, videos, $59 once</div></div></a>
<a href="#woo-photo" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">🏆 <b>Official plugin</b><div class="ex-nav-sub">WooCommerce Marketplace</div></div></a>
<a href="#cusrev" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">📬 <b>CusRev</b><div class="ex-nav-sub">The free all-rounder</div></div></a>
<a href="#yuko" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f5f3ff; border:1px solid #ddd6fe;">🔄 <b>Yuko</b><div class="ex-nav-sub">Judge.me replacement</div></div></a>
<a href="#villatheme" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🆓 <b>VillaTheme</b><div class="ex-nav-sub">The budget option</div></div></a>
<a href="#reviewx" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#fef2f2; border:1px solid #fecaca;">🎯 <b>ReviewX</b><div class="ex-nav-sub">Ratings in detail</div></div></a>
<a href="#scenarios" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🧭 <b>Which to choose</b><div class="ex-nav-sub">Six scenarios</div></div></a>
<a href="#faq" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">❓ <b>FAQ</b><div class="ex-nav-sub">Seven honest answers</div></div></a>
</div>
<p>Here is the short version of who is who. <b>PVR Media Reviews</b> is the newest and most complete plugin, and you pay once. <b>Photo Reviews for WooCommerce</b> is the official plugin from the WooCommerce Marketplace — $29 a year. <b>Customer Reviews for WooCommerce (CusRev)</b> is the most popular free option. <b>Yuko</b> was built for shops moving away from Judge.me. <b>Photo Reviews by VillaTheme</b> is the budget pick with a cheap one-time upgrade. <b>ReviewX</b> lets customers rate several things at once, but photos need a paid plan. One warning before we start: there are two different plugins named "Photo Reviews for WooCommerce" — one from the official WooCommerce Marketplace, one from VillaTheme on WordPress.org. Different products, different companies, different prices.</p>

<h2 id="why-photo">📸 Why photo reviews work better than text</h2>
<p>A five-star review makes a shopper feel a bit safer. A five-star review with a photo removes doubt: the buyer sees the product on a real desk, on a real person, in normal light — not in a studio render. For many visitors, that is the moment they decide to buy.</p>
<p>WooCommerce gives you basic text reviews with stars, and nothing else: no automatic "please leave a review" emails, no photo uploads, no way to reward customers with a discount. These six plugins fix that, each in its own way.</p>
<div class="ex-dashed"><b>The short answer:</b> a good photo review plugin (1) asks customers for photos automatically, (2) shows them nicely on the product page, (3) proves the reviewer really bought the item, and (4) fits your budget. The rest is details.</div>

<h2 id="comparison">⚖️ The six plugins at a glance</h2>
<p>The full scorecard is below — scores, prices and a one-line verdict for every plugin. We go through each plugin in detail after the table.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/photo-reviews/marketplace-banner.jpg" alt="Photo Reviews for WooCommerce — official Marketplace plugin" loading="lazy"><figcaption>Photo Reviews for WooCommerce — the official Marketplace extension. Banner: WooCommerce.</figcaption></figure>
<figure><img src="/storage/uploads/article/apps/photo-reviews/yuko-dashboard.png" alt="Yuko Reviews dashboard with review inbox and widget library" loading="lazy"><figcaption>Yuko Reviews: the Judge.me replacement with a modern dashboard and 8 display widgets. Screenshot: Yuko.</figcaption></figure>
</div>
[[comparison:COMPARISON_ID]]
<div class="ex-dashed"><b>How to read the table:</b> each score mixes several criteria — features and ease of use matter most, then speed and price, then support. "Free plan" and "Open source" are marked separately. Prices were checked in September 2026.</div>
<div class="ex-card ex-card-neutral" style="margin-top:14px;">
<div style="font-weight:700; margin-bottom:10px;">💸 What the paid plans cost over 3 years (photo review features)</div>
<svg viewBox="0 0 760 226" style="width:100%; height:auto;" role="img" aria-label="Total 3-year cost of paid plans">
<line x1="175" y1="8" x2="175" y2="222" stroke="#e2e8f0" stroke-width="2"/>
<text x="168" y="32" font-size="12" text-anchor="end" fill="#475569">VillaTheme Premium</text>
<rect x="175" y="16" width="8" height="24" rx="4" fill="#06b6d4"/><text x="191" y="32" font-size="12" font-weight="700" fill="#334155">$32 once</text>
<text x="168" y="68" font-size="12" text-anchor="end" fill="#475569">PVR Pro</text>
<rect x="175" y="52" width="15" height="24" rx="4" fill="#10b981"/><text x="198" y="68" font-size="12" font-weight="700" fill="#334155">$59 once (lifetime license)</text>
<text x="168" y="104" font-size="12" text-anchor="end" fill="#475569">Woo Marketplace</text>
<rect x="175" y="88" width="22" height="24" rx="4" fill="#3b82f6"/><text x="205" y="104" font-size="12" font-weight="700" fill="#334155">$87 ($29/yr)</text>
<text x="168" y="140" font-size="12" text-anchor="end" fill="#475569">CusRev Pro</text>
<rect x="175" y="124" width="46" height="24" rx="4" fill="#8b5cf6"/><text x="229" y="140" font-size="12" font-weight="700" fill="#334155">$180 ($59.99/yr)</text>
<text x="168" y="176" font-size="12" text-anchor="end" fill="#475569">Yuko Basic</text>
<rect x="175" y="160" width="111" height="24" rx="4" fill="#f59e0b"/><text x="294" y="176" font-size="12" font-weight="700" fill="#334155">$432 ($12/mo)</text>
<text x="168" y="212" font-size="12" text-anchor="end" fill="#475569">ReviewX Pro</text>
<rect x="175" y="196" width="440" height="24" rx="4" fill="#ef4444"/><text x="190" y="212" font-size="12" font-weight="700" fill="#fff">$1,706</text><text x="623" y="212" font-size="11" fill="#64748b">$47.40/mo × 36 mo</text>
</svg>
<div style="font-size:12px; color:#64748b; margin-top:6px;">Three-year total at list prices, September 2026. VillaTheme Premium and PVR Pro are one-time payments — no yearly fees. CusRev Pro is $59.99 + VAT per year. ReviewX is shown at the standard $47.40/month; its current promo ($79/year) would total $237 over three years. Woo Marketplace drops to about $75 over three years if you take its $46.40 two-year plan.</div>
</div>

<h2 id="pvr">📸 PVR Media Reviews — photos, videos and $59 once</h2>
<div class="ex-card ex-card-green"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #bbf7d0;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>9.5 · New (August 2026) · Pro from $59 once</div><b>🏆 Our pick: the most complete photo and video review plugin.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">Customers can leave photo and video reviews, videos are shrunk in the browser before upload, everything is stored on your own site, and the Pro version ($59 one-time) adds a reviews hub page that collects all reviews in one place for Google.</div></div>
<p style="margin:14px 0 18px;"><a href="https://demo.pv-reviews.site/" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; gap:8px; background:#ecfdf5; border:1px solid #6ee7b7; color:#047857; font-weight:700; font-size:14px; padding:9px 16px; border-radius:10px; text-decoration:none;">🔗 See the live demo</a><span style="font-size:13px; color:#64748b; margin-left:10px;">demo.pv-reviews.site — try the review form, gallery and reviews hub yourself</span></p>
<p><b>PVR Media Reviews</b> is the newest plugin in this comparison — and the most complete. Its standout feature: large videos shrink <b>inside the visitor's browser</b> before upload. Big video files never reach your server in full size, so cheap hosting stays fast and you pay nothing for video processing. Photos and reviews are stored like normal WordPress comments — remove the plugin tomorrow and your reviews stay.</p>
<p>The free version is unusually generous: photo and video reviews, reviewer avatars with a crop tool, "helpful" votes, shop replies, a photo and video gallery with a pop-up view, star ratings in Google results, spam protection, and 7 languages out of the box.</p>
<p>The <b>Pro version costs $59 once</b> — no yearly fee. It adds the extras that turn reviews into traffic: a <b>reviews hub page</b> that gathers all reviews with filters by category and manufacturer (search engines love such pages), custom ratings like "quality, value, durability" per product, automatic reminder emails, discount coupons for reviews, a stats dashboard, and carousel and grid widgets.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/pvr-reviews/review-form.png" alt="PVR Media Reviews review form with avatar upload and star ratings" loading="lazy"><figcaption>The review form: avatar with crop, star ratings, pros and cons, drag-and-drop photo and video uploads. Screenshot: PVR Media Reviews.</figcaption></figure>
<figure><img src="/storage/uploads/article/apps/pvr-reviews/video-compression.png" alt="PVR in-browser video compression with real-time progress" loading="lazy"><figcaption>A large video shrinks in the browser before it ever touches the server. Screenshot: PVR Media Reviews.</figcaption></figure>
</div>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/pvr-reviews/media-gallery.png" alt="PVR media gallery with all customer photos and videos" loading="lazy"><figcaption>The media gallery: every customer photo and video in one scrollable grid. Screenshot: PVR Media Reviews.</figcaption></figure>
<figure><img src="/storage/uploads/article/apps/pvr-reviews/reminders-coupons.png" alt="PVR automated review reminders and coupon incentives dashboard" loading="lazy"><figcaption>Reminder emails and coupon rewards (Pro): separate amounts for text, photo and video reviews. Screenshot: PVR Media Reviews.</figcaption></figure>
</div>
<p><b>Where PVR wins:</b></p>
<ul>
<li><b>Videos without extra load</b> — videos shrink in the visitor's browser before upload, so even cheap hosting copes.</li>
<li><b>Good for Google</b> — the reviews hub page collects all reviews in one place that search engines can index.</li>
<li><b>Your data stays yours</b> — reviews live in your own database, nothing goes to someone else's cloud.</li>
<li><b>Generous free version</b> — photos, videos, votes, replies, stars in Google and spam protection cost nothing.</li>
<li><b>Modern look</b> — clean gallery, carousel and grid widgets, review pop-ups with a built-in video player.</li>
</ul>
<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">💰 Pricing in practice:</div>
<div class="ex-inner">Free: photo and video reviews, avatars, votes, replies, stars in Google, spam protection — no limits. Pro: <b>$59 one-time</b> (early-bird lifetime license) — reviews hub page, custom ratings, reminders, coupons, stats, widgets. After the first 100 licenses, Pro moves to a yearly subscription.</div>
</div>
<p><b>Where PVR falls short:</b> it launched in August 2026 — almost no public reviews and installs yet, and a single developer behind it. Treat it like any young software: keep backups. Reminders, coupons and widgets are only in the Pro version.</p>
<div class="ex-dashed"><b>Who it is for:</b> shops that want photos and videos, want reviews to bring visitors from Google, and prefer paying once instead of every year.</div>

<hr>

<h2 id="woo-photo">🏆 Photo Reviews for WooCommerce — the official $29/year plugin</h2>
<div class="ex-card ex-card-green"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #bbf7d0;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>Official Marketplace · $29/yr · 30-day money-back</div><b>🏆 Our pick for pure photo review collection.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">The only plugin here built just for photo and video reviews: it crops images so the gallery looks tidy, sends reminder emails, and rewards photo reviews with discount coupons.</div></div>
<p><b>Photo Reviews for WooCommerce</b> by Xyno Tech is the official extension from the WooCommerce Marketplace. At <b>$29 per year</b> ($46.40 for two years) it covers the whole cycle: customers upload photos and videos with their review, the plugin crops images before showing them (free crop or fixed shapes: 1:1, 16:9, 4:3), and automatic emails chase customers who left text but forgot the photo.</p>
<p>Three kinds of emails go out on their own: a request after purchase, a reminder for customers who left text only, and a thank-you after a photo review. The coupon feature creates unique one-time discount codes for customers who add a photo — you pick the amount and the expiry date. In practice, this nudges many "text only" reviewers into adding a photo.</p>
<p>Five display styles come built in: the standard list (unchanged), a card grid, a minimal list, a "magazine" style where the newest review takes the full width, and a compact grid. Visitors can filter reviews by stars or "has photo" and vote the most helpful ones up.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/customer-reviews/banner-772x250.jpg" alt="Photo Reviews for WooCommerce plugin on the WooCommerce Marketplace" loading="lazy"><figcaption>Photo Reviews for WooCommerce — the official Marketplace extension. Banner: WooCommerce.</figcaption></figure>
</div>
<p><b>Where it wins:</b></p>
<ul>
<li><b>Photo cropping</b> — customers frame the photo before sending, so the gallery looks tidy, not random.</li>
<li><b>Coupons for photos only</b> — rewards photo reviews specifically, not just any review.</li>
<li><b>Verified purchases</b> — reviews are checked against real orders, so shoppers trust the photos.</li>
<li><b>One settings panel</b> — edit reviews, swap photos, change colors and layouts in one place.</li>
</ul>
<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">💸 Pricing in practice:</div>
<div class="ex-inner">One year: <b>$29</b>. Two years: <b>$46.40</b> (about 20% cheaper). 30-day money-back guarantee. No free version — but no per-order charges or limits either. For a dedicated photo review tool, $29 a year is the lowest yearly price in this comparison.</div>
</div>
<p><b>Where it falls short:</b> no free version, so you cannot try before paying. No questions-and-answers section, no reviews hub for Google, and no Google Shopping feeds (stars in search results come from WooCommerce itself). If you want the full review machine, CusRev does more — for free.</p>
<div class="ex-dashed"><b>Who it is for:</b> shops that mainly want photo reviews done well, with cropping and clean layouts, and don't mind paying $29 a year for a focused official tool.</div>

<hr>

<h2 id="cusrev">📬 Customer Reviews for WooCommerce — the free all-rounder</h2>
<div class="ex-card ex-card-green"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #bbf7d0;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>4.8 · 1,531 WordPress.org reviews · 80,000+ stores</div><b>🏆 Best free option overall — photo reviews cost nothing.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">The most-installed WooCommerce review plugin: unlimited reminder emails, photo and video reviews, discount coupons, trust badges, Google Shopping feeds and stars in search results — all free, with no limits.</div></div>
<p><b>Customer Reviews for WooCommerce</b> by CusRev is what WooCommerce's own review system should have been. The <b>free plan has no limits</b>: as many reminder emails as you want, photo and video reviews, reviews for the whole store (not just single products), discount coupons, moderation and public replies, trust badges, review votes, review search, and unsubscribe links.</p>
<p>Photos work simply: turn on image uploads in the settings, customers see an "Attach photos" button on the review form, and the photos show next to the review. You can even require a photo before the discount coupon is issued — a clean way to collect more pictures.</p>
<p>For Google it does more than most rivals: it creates <b>product review feeds for Google Shopping free listings</b>, adds star ratings to search results, and is translated into 36 languages. You can import and export reviews via CSV — including reviews from AliExpress or Etsy.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/customer-reviews/screenshot-3.jpg" alt="CusRev enhanced reviews section with photo gallery and rating summary" loading="lazy"><figcaption>Enhanced reviews section: photo gallery, rating summary bars, tags and sorting. Screenshot: CusRev.</figcaption></figure>
</div>
<p><b>Where CusRev wins:</b></p>
<ul>
<li><b>Photo reviews on the free plan</b> — image and video uploads included, no caps.</li>
<li><b>Coupons for reviews</b> — automatic discounts, including "photo required" rules.</li>
<li><b>Your data stays in your database</b> — uninstall the plugin and the reviews remain.</li>
<li><b>Google Shopping feeds</b> — the official Marketplace plugin does not do this.</li>
</ul>
<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">💸 Pricing in practice:</div>
<div class="ex-inner">Basic: free forever, with the full collection engine and photo reviews. Professional: <b>$59.99 + VAT per year</b> (or $7.99 + VAT per month) — your own "from" address and logo on emails, a visual email editor, custom review form questions, and email support.</div>
</div>
<p><b>Where CusRev falls short:</b> no photo cropping — pictures show exactly as the customer took them, so the gallery looks uneven. Display layouts are plain: a simple grid instead of the nicer "magazine" or card styles. And reminder emails depend on your hosting's mail setup — on cheap hosting they can quietly fail without a proper mail service.</p>
<div class="ex-dashed"><b>Who it is for:</b> shops that want the whole package free — reminders, coupons, photos, Google feeds — and can live with simpler layouts.</div>

<hr>

<h2 id="yuko">🔄 Yuko — for shops leaving Judge.me</h2>
<div class="ex-card ex-card-violet"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #ddd6fe;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>New · 8 widgets · one-click Judge.me import</div><b>🔄 Best if you are moving away from Judge.me.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">One click imports your Judge.me reviews with photos, ratings and dates intact. Review forms are made for phones, reminders go out automatically, and 8 display widgets include a full "Wall of Love" page.</div></div>
<p><b>Yuko</b> was built for one specific event: Judge.me shut down its WooCommerce version in October 2025, and thousands of shops lost their review tool. Yuko's pitch is simple — import everything from Judge.me in one click (text, stars, photos, names, dates) and keep working without rebuilding anything.</p>
<p>Everything is made for phones: the customer gets an email after the order ships, taps the link, and leaves a review with photos straight from the phone camera — no account needed. Yuko says 78% of reviews come in from mobile. The Basic plan ($12/month) adds photo reviews, coupons and a review carousel.</p>
<p>Eight display widgets ship across the plans: a stars badge, a review grid, a review list, a carousel, a sidebar widget, a full "Wall of Love" reviews page, and a photo gallery on higher plans. Stars in Google results work out of the box. The Advanced plan ($30/month) adds verified badges, a Google Shopping feed, Klaviyo sync and support for several stores under one account.</p>
<p><b>Where Yuko wins:</b></p>
<ul>
<li><b>One-click import</b> — from Judge.me, Yotpo or CSV; photos, ratings and dates survive, and the Yuko team helps with migration for free.</li>
<li><b>Phone-first forms</b> — a review takes under a minute on any phone, no login.</li>
<li><b>Most display options here</b> — 8 widgets, from a small stars badge to a full reviews page.</li>
<li><b>Several stores under one account</b> — with reviews shared across your shops.</li>
</ul>
<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">💸 Pricing in practice:</div>
<div class="ex-inner">Free: up to 50 orders/month, automatic requests, stars widget. Basic: <b>$12/month</b> (100 orders included, then +$5 per 100) — photo reviews, coupons, carousel. Advanced: <b>$30/month</b> (300 orders) — verified badges, photo gallery, Google Shopping, Klaviyo. Bigger stores: custom price. Monthly billing only, cancel anytime.</div>
</div>
<p><b>Where Yuko falls short:</b> your reviews live on Yuko's servers, not on your own site — if you ever leave, you need to export. The free plan has no photos at all; you need the $12 plan. And the company is young (launched late 2025), so betting your review history on it is a bigger leap than with CusRev's 80,000+ stores.</p>
<div class="ex-dashed"><b>Who it is for:</b> shops that used Judge.me and need their review history back, shops that want modern phone-first collection, and teams running several stores.</div>

<hr>

<h2 id="villatheme">🆓 Photo Reviews by VillaTheme — the budget option</h2>
<div class="ex-card ex-card-cyan"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #a5f3fc;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>4.7 · 77 WordPress.org reviews · 10,000+ installs</div><b>🆓 The cheapest start — with a catch.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">Photo uploads, reminder emails and automatic coupons on the free version — and a one-time ~$32 premium (no yearly fee) that adds AliExpress review import and smoother filtering.</div></div>
<p><b>Photo Reviews for WooCommerce</b> by VillaTheme is the original free photo review plugin, running on 10,000+ stores. The free version covers the basics: customers attach photos to reviews, you send reminder emails after delivery, the plugin generates unique coupons automatically (with a "photo required" option), and the review section shows a rating summary plus filters like "photo reviews only".</p>
<p>Display options in the free version are two styles — a plain list and a grid — with adjustable colors. A consent checkbox for public reviews (GDPR) is built in, and you can choose the sorting order and filter by stars.</p>
<p>The <b>premium version costs about $32 once</b> on CodeCanyon — no yearly fee. It removes the page reload when filtering, adds review titles, image folders, pop-up photo view and — the reason dropshippers buy it — <b>import of reviews with photos straight from AliExpress</b> by product ID, with filters and optional English translation.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/photo-reviews/villatheme-grid.jpg" alt="VillaTheme Photo Reviews grid layout with customer photos" loading="lazy"><figcaption>The grid layout with customer photos and rating filters. Screenshot: VillaTheme.</figcaption></figure>
</div>
<p><b>Where VillaTheme wins:</b></p>
<ul>
<li><b>Zero cost for the basics</b> — photos, reminders and coupons free, with no limits.</li>
<li><b>AliExpress import (premium)</b> — reviews with photos for dropshipping stores.</li>
<li><b>One-time payment</b> — the premium is ~$32 once, not a subscription.</li>
<li><b>GDPR checkbox built in</b> — in the free version, not locked behind premium.</li>
</ul>
<div class="ex-card ex-card-orange"><b>⚠️ The catch:</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">The free version reloads the page on every filter click, has no review titles and no pop-up photo view. The premium is a one-time purchase, but updates depend on one developer, and recent updates were mostly about keeping compatibility rather than new features. No Google Shopping feed either.</div></div>
<p><b>Where VillaTheme falls short:</b> only two basic layouts in the free version, simpler reminder emails than CusRev (fewer scheduling options), support through the WordPress.org forums can be slow, and video reviews are premium-only.</p>
<div class="ex-dashed"><b>Who it is for:</b> shops on a tight budget, dropshippers who need AliExpress review import, and anyone who prefers a one-time payment over a yearly fee.</div>

<hr>

<h2 id="reviewx">🎯 ReviewX — customers rate several things at once</h2>
<div class="ex-card ex-card-orange-strong"><div class="ex-card-head"><div style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #fecaca;border-radius:999px; padding:4px 10px; font-size:13px; font-weight:700; color:#b45309;"><span style="color:#f59e0b;">★</span>4.5 · 85 WordPress.org reviews · 7,000+ installs</div><b>🎯 Rate quality, price and delivery separately — photos included.</b></div><div style="margin-top:8px; font-size:14px; line-height:1.6;">Instead of one overall star rating, customers rate several things — quality, value for money, delivery — and attach photos or videos. Note: a security hole was found in June 2026 and fixed in version 2.3.11 — update before you start collecting reviews.</div></div>
<p><b>ReviewX</b> stands out with a different idea: instead of one star rating, customers rate <b>several things separately</b> — product quality, value for money, delivery speed — and can attach photos and videos. That gives you more detailed feedback and nicer rating bars on the product page. Photo uploads, though, are only in the Pro version.</p>
<p>The free version includes multi-criteria ratings (up to 3 criteria), reminder emails, verified badges, social sharing and widgets for the Elementor page builder. The Pro version at <b>$47.40/month</b> (or <b>$79/year</b> at the current promo price) adds photo and video reviews, stars in Google results, coupons, shop replies and spam protection.</p>
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/photo-reviews/reviewx-criteria.png" alt="ReviewX multi-criteria rating interface" loading="lazy"><figcaption>ReviewX: customers rate quality, fit and value separately, with photo and video uploads. Screenshot: ReviewX.</figcaption></figure>
</div>
<div class="ex-card ex-card-orange-strong"><b>⚠️ Security note:</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">In June 2026 a serious hole was found in ReviewX versions up to 2.3.10: strangers could inject malicious code through the review form. It was fixed in version 2.3.11. If you use ReviewX, <b>update before anything else</b> — check the version in your WordPress admin under Plugins.</div></div>
<p><b>Where ReviewX wins:</b></p>
<ul>
<li><b>Detailed ratings</b> — customers rate quality, fit and value separately, with photos next to each — no other plugin here does this.</li>
<li><b>Page builder widgets</b> — works with Elementor, Divi and Oxygen out of the box.</li>
<li><b>Social sharing</b> — customers can share their review to social media in one click.</li>
</ul>
<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">💸 Pricing in practice:</div>
<div class="ex-inner">Free: detailed ratings, reminders, badges — no photos. Pro: <b>$47.40/month</b> or <b>$79/year</b> (current promo) — photo and video reviews, stars in Google, coupons, replies, spam protection. Agencies: $119.40/year for 5 sites, $239.40/year for 50 sites.</div>
</div>
<p><b>Where ReviewX falls short:</b> photos need the paid plan — the free version collects no pictures at all. At $47.40/month it is the most expensive subscription in this comparison; the $79/year promo is the only sensible way in. The store base is small (7,000+ installs, 85 public reviews), and you must check the security update before trusting it with customer input.</p>
<div class="ex-dashed"><b>Who it is for:</b> shops that specifically want customers to rate quality, fit and value separately, and are happy to pay for it. If you only need photos, pick a different plugin.</div>

<hr>

<h2 id="scenarios">🧭 Which plugin should you pick? Six scenarios</h2>
<p>Scores are one thing; your situation is another. Here are the six most common cases:</p>
<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">📸 "I want photos AND videos, and reviews that bring Google traffic."</div><div style="font-size:14px; line-height:1.6;">Take <b>PVR Media Reviews</b>. Video shrinks in the browser, reviews stay on your site, and the $59 Pro (once) adds a reviews hub page for Google.</div></div>
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">🏆 "I want photo reviews done neatly, cropping included."</div><div style="font-size:14px; line-height:1.6;">Take <b>Photo Reviews for WooCommerce</b> (official). Cropping, five layouts, coupons for photos, verified badges — $29/year.</div></div>
<div class="ex-card ex-card-neutral"><div style="font-weight:700; margin-bottom:8px;">💰 "I want photos, reminders and coupons without paying anything."</div><div style="font-size:14px; line-height:1.6;">Install <b>Customer Reviews for WooCommerce</b> (CusRev). Everything above is free, plus Google Shopping feeds. Upgrade ($59.99/yr) only when you want your own branding on emails.</div></div>
<div class="ex-card ex-card-violet"><div style="font-weight:700; margin-bottom:8px;">🔄 "I lost my reviews when Judge.me left WooCommerce."</div><div style="font-size:14px; line-height:1.6;">Go <b>Yuko</b>. One click brings over reviews, photos, ratings and dates from Judge.me. Free up to 50 orders/month, then $12/month.</div></div>
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">🆓 "I want the cheapest possible start."</div><div style="font-size:14px; line-height:1.6;">Start with <b>VillaTheme's free version</b>: photos, reminders, coupons at $0. If you outgrow it, the premium is ~$32 once — not a subscription.</div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:8px;">🎯 "I want customers to rate quality, price and delivery separately."</div><div style="font-size:14px; line-height:1.6;">Take <b>ReviewX</b> Pro ($79/year promo). Detailed ratings with photos and videos. But update to 2.3.11+ first — and check the current price.</div></div>
</div>

<h2 id="faq">❓ Frequently asked questions</h2>
<div class="ex-card ex-card-neutral"><b>How is PVR Media Reviews different from the others?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">It is the most complete: photo and video reviews, videos shrunk in the browser, a reviews hub page for Google, custom ratings, reminders, coupons and widgets. The free version already covers photos, videos, votes and replies. Pro is $59 once. The trade-off: it is new (August 2026) and run by one developer, so fewer stores use it so far.</div></div>
<div class="ex-card ex-card-neutral"><b>Two plugins are named "Photo Reviews for WooCommerce" — what is the difference?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">They are different products from different companies. The <b>Marketplace version</b> by Xyno Tech costs $29/year and adds photo cropping, five layouts and verified badges. The <b>VillaTheme version</b> on WordPress.org is free with basic photo uploads, and its premium (~$32 once on CodeCanyon) adds AliExpress import. If you can spend $29 a year, the Marketplace version is the more polished one; if free is the requirement, take VillaTheme.</div></div>
<div class="ex-card ex-card-neutral"><b>Do these plugins replace the normal WooCommerce reviews?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Partly. The Marketplace plugin, VillaTheme and ReviewX add features to the normal WooCommerce review form — reviews stay in your database. CusRev replaces the review form and emails but keeps reviews in your database too. Yuko is the odd one: reviews live on Yuko's servers. And WooCommerce's built-in reviews always remain as a fallback if you uninstall any of these plugins.</div></div>
<div class="ex-card ex-card-neutral"><b>Will my reviews show stars in Google search results?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Depends on the plugin. CusRev adds stars in search results and creates Google Shopping feeds. Yuko does it automatically. ReviewX does it on the Pro plan. The Marketplace plugin and VillaTheme rely on what WooCommerce itself outputs. None of them provide Google Seller Ratings for paid ads.</div></div>
<div class="ex-card ex-card-neutral"><b>Can I import reviews from AliExpress or another platform?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">VillaTheme premium imports reviews with photos from AliExpress by product ID. CusRev imports from CSV (including AliExpress and Etsy exports). Yuko imports from Judge.me, Yotpo and CSV. The Marketplace plugin and ReviewX have no import. The universal recipe: export to CSV, make sure the photo links work, and double-check the "verified purchase" badges after import.</div></div>
<div class="ex-card ex-card-neutral"><b>Which plugin is best for a brand-new store with no money?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Two good answers: <b>PVR Media Reviews</b> free (photos, videos, votes, replies, stars in Google) or <b>CusRev</b> free (reminders, coupons, Google Shopping feeds). Both cost nothing with no limits. Add the official Marketplace plugin ($29/yr) later when photo cropping and nicer layouts become important.</div></div>
<div class="ex-card ex-card-neutral"><b>Is ReviewX safe to use now?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Yes — if you update to version 2.3.11 or newer. The June 2026 hole let strangers inject malicious code through the review form, but it was patched quickly. Check your version in the WordPress admin under Plugins, update, and you are fine. If your site faces attacks, the older temporary fix was a firewall rule — but the update alone is enough today.</div></div>

<h2 id="verdict">🔎 Final verdict</h2>
<div class="ex-summary-bg">
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:16px;">
<div class="ex-summary-card" style="border-left:4px solid #10b981;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">📸 PVR Media Reviews — 9.5</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The most complete tool: photos and videos, videos shrunk in the browser, a reviews hub for Google, widgets, and everything stored on your own site. Pro is $59 once.</div><div class="ex-inner-green">The best long-term deal — pay once, keep everything.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #06b6d4;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">📬 CusRev — 8.8</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The best free option: photo reviews, unlimited reminders, coupons, Google Shopping feeds and 80,000+ stores of proven track record.</div><div class="ex-inner-cyan">Start here if you want everything included at $0.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #3b82f6;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🏆 Official Marketplace plugin — 8.6</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The tidy specialist: photo cropping, five layouts, coupons for photo reviews, verified badges — $29/year.</div><div class="ex-inner-cyan">The best dedicated photo tool at the lowest yearly price.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #8b5cf6;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🔄 Yuko — 8.0</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The Judge.me replacement: one-click import, phone-first forms, 8 widgets. Free up to 50 orders/month, then $12/month.</div><div class="ex-inner-violet">The obvious choice if you are moving from Judge.me.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #f59e0b;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🆓 VillaTheme — 7.8 · 🎯 ReviewX — 7.2</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">VillaTheme is the cheapest entry (free core, ~$32 premium once). ReviewX has the most detailed ratings but needs Pro for photos — and a security update first.</div><div class="ex-inner-orange">Pick by scenario, not by score.</div></div>
</div>
</div>
<div class="ex-card ex-card-green-strong"><div style="font-weight:700;">In short: <b>PVR Media Reviews</b> is the most complete tool and the best long-term deal ($59 once). <b>CusRev</b> is the best free option with coupons and Google feeds. The <b>official Marketplace plugin</b> is the neat $29-a-year photo specialist. <b>Yuko</b> is for Judge.me refugees. <b>VillaTheme</b> is the budget pick, and <b>ReviewX</b> only makes sense if you want customers to rate several things at once. A sensible path: start free with PVR or CusRev, and pay only when you feel the limit.</div></div>
<style>
@keyframes exAuroraFlow{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
.ex-cta-aurora{position:relative;overflow:hidden;border-radius:16px;padding:28px 22px;text-align:center;color:#fff;background:linear-gradient(115deg,#0f172a,#1e40af,#7c3aed,#0e7490,#10b981,#0f172a);background-size:400% 400%;animation:exAuroraFlow 75s ease infinite;box-shadow:0 12px 32px rgba(15,23,42,.28)}
.ex-cta-aurora>*{position:relative;z-index:1}
</style>
<div class="ex-cta-aurora">
<div>
<div style="font-size:24px; font-weight:700; margin-bottom:12px;">Photo reviews are the cheapest sales booster you are not using</div>
<div style="font-size:16px; line-height:1.6; opacity:0.95; max-width:720px; margin:0 auto;">
Every customer photo sells the product for you twice: once on the product page, and again in Google with star ratings next to your listing. Pick a plugin that keeps collecting while you sleep — and keeps the reviews yours.
<div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.25);">Explore the tool cards above — every plugin on this list has a full profile with criteria scores on this site.</div>
</div>
</div>
</div>
HTML;
}
