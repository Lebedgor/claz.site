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

class JudgeMeVsLooxSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::withTrashed()->where('slug->en', 'judge-me-vs-loox')->exists()) {
            $this->command->info('Article already exists — skipped to preserve admin edits. Delete the article to rebuild it from the seeder.');

            return;
        }

        $category = Category::query()->where('slug->en', 'shopify-apps')->first()
            ?? Category::create([
                'name' => ['en' => 'Shopify apps'],
                'slug' => ['en' => 'shopify-apps'],
                'description' => ['en' => 'Honest reviews and comparisons of apps that make Shopify stores sell more.'],
                'sort_order' => 30,
            ]);

        foreach (['shopify', 'product-reviews', 'ecommerce', 'photo-reviews'] as $tagSlug) {
            Tag::query()->where('slug->en', $tagSlug)->first()
                ?? Tag::create(['slug' => ['en' => $tagSlug], 'name' => ['en' => str_replace('-', ' ', $tagSlug)]]);
        }

        $tools = [];
        $criteria = [];
        foreach (Criterion::query()->orderBy('sort_order')->get() as $criterion) {
            $criteria[$criterion->getTranslation('name', 'en')] = $criterion;
        }

        $toolData = [
            'Judge.me' => [
                'type' => ToolType::Plugin,
                'vendor' => 'Judge.me',
                'rating' => 9.4,
                'description' => 'The most-reviewed Shopify review app: unlimited reviews and photo/video UGC on every plan, a flat $15 paid tier with no order caps, AI summaries and replies in 38 languages, and syndication to Google Shopping, Meta, TikTok and Shop App.',
                'affiliate' => false,
                'url' => 'https://judge.me',
                'values' => ['Ease of use' => 9, 'Features' => 9, 'Performance' => 9, 'Pricing' => 9, 'Support & docs' => 9, 'Free plan' => true, 'Open source' => false],
            ],
            'Loox' => [
                'type' => ToolType::Service,
                'vendor' => 'Loox',
                'rating' => 8.2,
                'description' => 'A visual-first review platform for Shopify: photo and video collection with AI smart sorting, 17+ display widgets, referral programs and post-purchase upsells — but usage-based pricing that scales with order volume and converts at $49.99/month.',
                'affiliate' => false,
                'url' => 'https://loox.app',
                'values' => ['Ease of use' => 9, 'Features' => 9, 'Performance' => 8, 'Pricing' => 6, 'Support & docs' => 8, 'Free plan' => true, 'Open source' => false],
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
            'title' => ['en' => 'Judge.me vs Loox (2026): The Honest Comparison for Shopify Stores'],
            'slug' => ['en' => 'judge-me-vs-loox'],
            'excerpt' => ['en' => 'Judge.me and Loox are the two most popular Shopify review apps. We compared pricing, photo review collection, AI features, widgets, security and real-world costs to find the right pick for every store size.'],
            'body_html' => ['en' => self::ARTICLE_BODY],
            'cover' => 'uploads/article/apps/customer-reviews/banner-1544x500.jpg',
            'status' => ArticleStatus::Published->value,
            'published_at' => now(),
            'editor_mode' => EditorMode::Html->value,
            'meta_title' => ['en' => 'Judge.me vs Loox: Which Shopify Review App Wins in 2026?'],
            'meta_description' => ['en' => 'Honest comparison of Judge.me vs Loox: pricing, features, photo reviews, AI tools, security and which Shopify review app is actually better for your store.'],
        ]);

        $tagIds = Tag::query()->whereIn('slug->en', ['shopify', 'product-reviews', 'ecommerce', 'photo-reviews'])->pluck('id');
        $article->tags()->sync($tagIds);

        $comparison = $article->comparisons()->create([
            'title' => ['en' => 'Judge.me vs Loox: head-to-head'],
            'intro' => ['en' => 'Criteria scores from our testing methodology. Pricing, features and App Store ratings are current as of September 2026.'],
            'verdict' => ['en' => 'Judge.me wins on value, security and scale economics. Loox wins on visual widget quality and AI-powered conversion tools. The right choice depends on your order volume and how much you value flat pricing versus visual polish.'],
            'sort_order' => 0,
        ]);

        $items = [
            ['Judge.me' => ['score' => 9.4, 'verdict' => 'Best value — flat $15/mo with unlimited reviews, no order caps, and the highest security rating in the category']],
            ['Loox' => ['score' => 8.2, 'verdict' => 'Best visual experience — 17+ widgets, AI smart sorting, and referral programs, but usage-based pricing that scales unpredictably']],
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
<p style="margin:0 0 18px; font-size:17px;"><b>Judge.me</b> and <b>Loox</b> are the two review apps most Shopify merchants compare — and for good reason. Both collect photo and video reviews, both display them in customizable widgets, and both claim to boost conversions through social proof. But they follow fundamentally different philosophies: Judge.me bets on flat, transparent pricing with unlimited volume, while Loox invests heavily in visual polish, AI-powered sorting and built-in referral programs. The difference between them is not which is "better" in the abstract — it is which pricing model and feature set actually fits your store at your current scale.</p>
<div class="ex-nav-row">
<a href="#pricing" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">💰 <b>Pricing</b><div class="ex-nav-sub">Flat vs usage-based</div></div></a>
<a href="#collection" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">📬 <b>Collection</b><div class="ex-nav-sub">How reviews come in</div></div></a>
<a href="#display" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f5f3ff; border:1px solid #ddd6fe;">🖼️ <b>Display</b><div class="ex-nav-sub">Widgets and visual quality</div></div></a>
<a href="#ai" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#fff7ed; border:1px solid #fed7aa;">🤖 <b>AI features</b><div class="ex-nav-sub">Smart tools and automation</div></div></a>
<a href="#conversion" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">📈 <b>Conversion tools</b><div class="ex-nav-sub">Referrals, upsells, coupons</div></div></a>
<a href="#security" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#fef2f2; border:1px solid #fecaca;">🔒 <b>Security</b><div class="ex-nav-sub">Data protection and compliance</div></div></a>
<a href="#scenarios" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#ecfeff; border:1px solid #a5f3fc;">🧭 <b>Which to choose</b><div class="ex-nav-sub">Four scenarios</div></div></a>
<a href="#faq" style="text-decoration:none; color:inherit;"><div class="ex-nav-btn" style="background:#f0fdf4; border:1px solid #bbf7d0;">❓ <b>FAQ</b><div class="ex-nav-sub">Five honest answers</div></div></a>
</div>

<h2 id="pricing">💰 Pricing: the single biggest difference</h2>
<p>The pricing models are not just different in amount — they are different in kind. Judge.me charges a flat monthly fee regardless of how many orders your store processes. Loox charges a base fee plus usage-based overage that scales with your order volume. This distinction matters more than any feature comparison, because it determines what your review app costs at 100 orders per month, 1,000 orders per month and 10,000 orders per month.</p>

<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:10px;">Judge.me</div><div style="font-size:14px; line-height:1.8;">
<b>Free:</b> $0 — unlimited reviews, unlimited requests, unlimited photo/video<br>
<b>Awesome:</b> $15/month — AI, referrals, coupons, Google Shopping, full customization<br>
<b>Annual:</b> no annual plan exists — monthly billing only<br>
<b>Overage:</b> none — $15 is the maximum you will ever pay
</div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:10px;">Loox</div><div style="font-size:14px; line-height:1.8;">
<b>Beginner:</b> free — up to 500 total orders, photo reviews, 17+ widgets, 2,000 requests/mo<br>
<b>Convert:</b> $49.99/month — 300 orders included, then +$50 per 300 extra orders<br>
<b>Unlimited:</b> $299.99/month — unlimited orders, priority support<br>
<b>Overage:</b> $50 per 300 orders beyond the included amount (capped at $799 on Convert)
</div></div>
</div>

<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">📊 What this costs at different scales:</div>
<svg viewBox="0 0 760 220" style="width:100%; height:auto;" role="img" aria-label="Monthly cost comparison at different order volumes">
<text x="170" y="18" font-size="12" font-weight="700" fill="#334155">Orders/mo</text>
<text x="310" y="18" font-size="12" font-weight="700" fill="#334155">Judge.me</text>
<text x="460" y="18" font-size="12" font-weight="700" fill="#334155">Loox Convert</text>
<text x="170" y="46" font-size="12" text-anchor="end" fill="#475569">100</text>
<text x="310" y="46" font-size="12" font-weight="600" fill="#10b981">$0 (free)</text>
<text x="460" y="46" font-size="12" font-weight="600" fill="#10b981">$0 (free, under 500)</text>
<text x="170" y="72" font-size="12" text-anchor="end" fill="#475569">500</text>
<text x="310" y="72" font-size="12" font-weight="600" fill="#10b981">$15</text>
<text x="460" y="72" font-size="12" font-weight="600" fill="#f59e0b">$49.99</text>
<text x="170" y="98" font-size="12" text-anchor="end" fill="#475569">1,000</text>
<text x="310" y="98" font-size="12" font-weight="600" fill="#10b981">$15</text>
<text x="460" y="98" font-size="12" font-weight="600" fill="#f59e0b">$149.99</text>
<text x="170" y="124" font-size="12" text-anchor="end" fill="#475569">3,000</text>
<text x="310" y="124" font-size="12" font-weight="600" fill="#10b981">$15</text>
<text x="460" y="124" font-size="12" font-weight="600" fill="#ef4444">$449.99</text>
<text x="170" y="150" font-size="12" text-anchor="end" fill="#475569">5,000</text>
<text x="310" y="150" font-size="12" font-weight="600" fill="#10b981">$15</text>
<text x="460" y="150" font-size="12" font-weight="600" fill="#ef4444">$749.99 (cap)</text>
<text x="170" y="180" font-size="11" fill="#64748b">Note: Loox counts ALL Shopify orders (including marketplace integrations), not just orders from your online store</text>
<text x="170" y="200" font-size="11" fill="#64748b">Judge.me Awesome pricing verified September 2026. Loox Convert pricing verified September 2026.</text>
</svg>
</div>

<div class="ex-dashed"><b>The real-world pricing trap:</b> Loox's Convert plan includes 300 orders in the base $49.99, then charges $50 per additional 300 orders. But "orders" includes all Shopify orders — including those from marketplace integrations like Amazon and Mercado Libre that have nothing to do with Loox's review collection. Multiple merchants on the Shopify App Store report being charged $300–$400+ per month on what they expected to be a $50 plan. Judge.me has no equivalent surprise: $15 is $15, regardless of order volume.</div>

<h2 id="collection">📬 Review collection: how reviews come in</h2>
<p>Both apps automate the review request pipeline, but the mechanics differ. Judge.me sends unlimited review requests on every plan, including the free tier. Loox's Beginner plan caps at 2,000 review request emails per month (raised from 100 in July 2026), while the Convert plan offers unlimited requests.</p>

<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">Judge.me collection</div><div style="font-size:14px; line-height:1.8;">
✅ Unlimited review requests (free + paid)<br>
✅ Email, SMS, QR code, push notifications<br>
✅ Photo and video reviews on free plan<br>
✅ Automatic scheduling with smart timing<br>
✅ Custom schedule per product<br>
✅ Past orders & custom lists<br>
✅ Bundle review collection<br>
✅ Store reviews alongside product reviews<br>
✅ Shopify Customer Accounts integration
</div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:8px;">Loox collection</div><div style="font-size:14px; line-height:1.8;">
✅ Automated review request emails<br>
✅ Delivery-based scheduling<br>
✅ Photo reviews on free plan<br>
✅ Video reviews on Convert plan ($49.99+)<br>
✅ Discount codes for photo reviews<br>
✅ Bonus reminder emails<br>
✅ Custom email banners and appearance<br>
✅ 2,000 requests/mo on free (raised Jul 2026)<br>
⚠️ No SMS or QR code collection
</div></div>
</div>

<div class="ex-card ex-card-neutral"><b>Key difference:</b> Judge.me's free plan includes unlimited photo and video reviews with unlimited request emails. Loox's free plan includes photo reviews but not video (that requires Convert at $49.99/month), and caps review requests at 2,000 per month. For stores just starting out, Judge.me's free collection engine is categorically more generous.</div>

<h2 id="display">🖼️ Display widgets: visual quality and variety</h2>
<p>This is where Loox earns its reputation. Both apps offer multiple display formats, but Loox invests more heavily in visual design and widget variety — and the difference is visible on the storefront.</p>

<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">Judge.me display</div><div style="font-size:14px; line-height:1.8;">
<b>Widgets:</b> Review Widget, Star Rating Badge, Cards Carousel, Testimonial Carousel, Video Carousel, Reviews Grid, UGC Instagram Shopping, Medals, Trust Badge, Pop-up, Floating Sidebar, AI Reviews Summary, Review Snippets<br>
<b>Happy Customers Page:</b> all reviews on one SEO-optimized page<br>
<b>Customization:</b> themes, custom CSS, star icons, multilingual sorting<br>
<b>Placement:</b> product pages, homepage, collections, custom pages via Liquid
</div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:8px;">Loox display</div><div style="font-size:14px; line-height:1.8;">
<b>Widgets:</b> 17+ including Popup, Carousel, Happy Customers, Star Rating, Snippets, Trust Badge, Sidebar, Review Stories (AI), Reviews Grid, Instagram Shopping<br>
<b>AI Review Stories:</b> swipeable visual summaries auto-generated by AI<br>
<b>Theme Matcher:</b> AI automatically matches widget fonts and colors to your theme<br>
<b>Customization:</b> widget fonts, colors, advanced email customization, custom questions<br>
<b>Placement:</b> product pages, homepage, collections, checkout (coming soon)
</div></div>
</div>

<div class="ex-card ex-card-bordered"><div style="font-weight:700; font-size:16px; margin-bottom:10px;">🏆 Widget verdict:</div>
<div class="ex-inner">Loox's widgets are genuinely more visually polished. The AI Review Stories format — swipeable, Instagram-style cards that summarize the best reviews — is a display format Judge.me does not offer. Loox's Theme Matcher automatically aligns widget styling with your store's design, which saves customization time. Judge.me's widgets are functional and well-built, but they look more "standard" next to Loox's conversion-optimized designs. For stores where visual presentation is the primary differentiator (fashion, beauty, lifestyle), Loox's widget quality justifies attention.</div>
<div class="ex-inner-orange">However, widget quality alone does not justify a 3x–50x price difference. Judge.me's widgets convert just fine for most stores — the question is whether Loox's visual edge produces enough additional revenue to cover the cost gap.</div>
</div>

<h2 id="ai">🤖 AI features: smart tools and automation</h2>
<p>Both apps have invested in AI, but the feature sets diverge at the pricing tier. Judge.me includes AI summaries, AI replies and AI sentiment analysis on the $15 Awesome plan. Loox gates its AI features behind the $49.99 Convert plan — and the AI capabilities are more extensive.</p>

<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">Judge.me AI ($15/mo)</div><div style="font-size:14px; line-height:1.8;">
✅ AI product review summaries<br>
✅ AI review highlights and keywords<br>
✅ AI-generated reply suggestions<br>
✅ AI sentiment and topics analysis<br>
✅ AI auto-publish (moderation)<br>
✅ Content translation (38 languages)<br>
✅ Sidekick AI assistant integration
</div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:8px;">Loox AI ($49.99/mo)</div><div style="font-size:14px; line-height:1.8;">
✅ AI Smart Sorting (surfaces highest-converting photos first)<br>
✅ AI Review Highlights (pulls persuasive sentences)<br>
✅ AI Summaries (scannable review summaries above widgets)<br>
✅ AI Review Stories (swipeable visual summaries)<br>
✅ AI Translations (38 languages)<br>
✅ AI Replies (response suggestions)<br>
✅ AI Theme Matcher (auto-aligns widget design)
</div></div>
</div>

<div class="ex-card ex-card-neutral"><b>Assessment:</b> Loox's AI suite is broader — Smart Sorting and Review Stories are genuinely novel features that Judge.me lacks. But Judge.me's AI covers the essentials (summaries, replies, translations, sentiment) at a fraction of the cost. For most stores, Judge.me's AI at $15/month covers what you actually need. Loox's AI is for stores that want the cutting edge of visual conversion optimization and are willing to pay $50+/month for it.</div>

<h2 id="conversion">📈 Conversion tools: referrals, upsells and coupons</h2>
<p>Beyond reviews, both apps include tools designed to generate revenue directly. Judge.me includes coupons and referrals on the Awesome plan. Loox includes referrals, post-purchase upsells and next-purchase discounts — but again, the breadth depends on your plan.</p>

<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">Judge.me conversion tools ($15/mo)</div><div style="font-size:14px; line-height:1.8;">
✅ Coupons for leaving reviews<br>
✅ Referral program (one-click share)<br>
✅ Google Shopping syndication<br>
✅ Meta & TikTok Shop syndication<br>
✅ Shop App sync<br>
✅ Email content optimization<br>
✅ Product recommendations in emails
</div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:8px;">Loox conversion tools ($49.99/mo)</div><div style="font-size:14px; line-height:1.8;">
✅ Discount codes for photo reviews<br>
✅ Referrals: on-site, post-purchase, post-review<br>
✅ Post-purchase Smart Upsell<br>
✅ Next-purchase discounts<br>
✅ Google, Meta, TikTok syndication<br>
✅ Shop App syndication<br>
✅ API & webhooks for custom integrations
</div></div>
</div>

<div class="ex-card ex-card-neutral"><b>Key difference:</b> Loox's referral program is more mature — three placement options (on-site, post-purchase, post-review) versus Judge.me's single-click referral. Loox also includes post-purchase upsells, which Judge.me does not offer. But Judge.me's conversion tools cost $15/month; Loox's cost $49.99/month minimum. For stores that primarily need reviews and basic referral functionality, Judge.me covers the bases. For stores building a full social proof + referral + upsell stack, Loox is the more complete platform.</div>

<h2 id="security">🔒 Security and data protection</h2>
<p>This is an area where Judge.me has a measurable, documented advantage that Loox has not publicly matched.</p>

<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">Judge.me security</div><div style="font-size:14px; line-height:1.8;">
🏆 <b>SecurityScorecard rating: 96 (A)</b> — highest among review apps<br>
✅ ISO 27001 certified<br>
✅ SOC 2 Type 2 certified<br>
✅ Enterprise-grade data protection<br>
✅ GDPR compliant<br>
✅ Reviews stored on Judge.me infrastructure with encrypted transit
</div></div>
<div class="ex-card ex-card-neutral"><div style="font-weight:700; margin-bottom:8px;">Loox security</div><div style="font-size:14px; line-height:1.8;">
⚠️ SecurityScorecard rating: 54 (F) — per Judge.me's comparison page<br>
✅ Anti-theft protection for review widgets<br>
✅ Allowed-list external domains<br>
✅ GDPR compliant<br>
⚠️ No publicly listed ISO or SOC certifications<br>
✅ Reviews stored on Loox infrastructure
</div></div>
</div>

<div class="ex-dashed"><b>Important context:</b> Judge.me publishes its SecurityScorecard rating (96/A) and certifications (ISO 27001, SOC 2 Type 2) publicly. Loox does not publicly list equivalent certifications. The SecurityScorecard ratings cited here are from Judge.me's comparison page — we were unable to independently verify Loox's score on SecurityScorecard's public directory. For stores handling sensitive customer data or operating under compliance requirements (HIPAA-adjacent, PCI-scoped), Judge.me's documented security posture is a meaningful differentiator.</div>

<h2 id="scenarios">🧭 Which should you choose? Four scenarios</h2>
<p>The right choice depends on your store's size, budget and priorities. These are the four most common situations:</p>

<div class="ex-grid">
<div class="ex-card ex-card-green"><div style="font-weight:700; margin-bottom:8px;">💰 "I want the best review app at the lowest cost."</div><div style="font-size:14px; line-height:1.6;">Choose <b>Judge.me</b>. The free plan includes unlimited reviews, unlimited requests, photo and video uploads, and Google rich snippets — features that Loox gates behind $49.99/month. The Awesome plan at $15/month covers AI, referrals, coupons and full customization. No order caps, no usage fees, no surprises.</div></div>
<div class="ex-card ex-card-orange"><div style="font-weight:700; margin-bottom:8px;">📸 "Visual presentation is my priority — I sell fashion, beauty or lifestyle."</div><div style="font-size:14px; line-height:1.6;">Consider <b>Loox</b>. The 17+ widgets, AI Review Stories and Theme Matcher produce a more visually polished review section than Judge.me's functional displays. But verify the total cost first: at 1,000 orders/month, Loox Convert costs $150/month versus Judge.me's $15.</div></div>
<div class="ex-card ex-card-violet"><div style="font-weight:700; margin-bottom:8px;">📈 "I want reviews + referrals + upsells in one platform."</div><div style="font-size:14px; line-height:1.6;"><b>Loox</b> offers the more complete conversion toolkit: three referral placements, post-purchase upsells and next-purchase discounts. Judge.me includes referrals and coupons but not upsells. If you need all three, Loox is the more integrated option — at the Convert plan price.</div></div>
<div class="ex-card ex-card-cyan"><div style="font-weight:700; margin-bottom:8px;">🔒 "Security and compliance matter — I handle sensitive customer data."</div><div style="font-size:14px; line-height:1.6;"><b>Judge.me</b> holds ISO 27001, SOC 2 Type 2 and a 96/A SecurityScorecard rating. Loox has not publicly listed equivalent certifications. For stores under compliance requirements or handling regulated data, Judge.me's documented security posture is the safer choice.</div></div>
</div>

<h2 id="faq">❓ Frequently asked questions</h2>
<div class="ex-card ex-card-neutral"><b>Can I migrate from Loox to Judge.me (or vice versa)?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Yes. Judge.me imports reviews from Loox (and Yotpo, Stamped, Amazon, Etsy, AliExpress) via CSV or direct import. Loox also imports from Judge.me and other apps in a few clicks. Both migrations preserve review text, photos, star ratings and dates. Verified buyer badges may reset because the purchase happened on the other platform. Plan the migration during a low-traffic period and keep the old app active in display-only mode until the new widgets are verified.</div></div>
<div class="ex-card ex-card-neutral"><b>Does Loox's pricing include all Shopify orders or only online store orders?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">This is the most common complaint about Loox's billing. According to merchant reports on the Shopify App Store, Loox counts ALL Shopify orders — including those from marketplace integrations (Amazon, Mercado Libre, etc.) — toward your plan's order limit. This means a store with 500 online orders and 2,000 marketplace orders would be billed for 2,500 orders on the Convert plan, resulting in charges well above the $49.99 base. Judge.me does not have usage-based pricing, so this issue does not apply.</div></div>
<div class="ex-card ex-card-neutral"><b>Which app has better Shopify App Store reviews?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Judge.me: 5.0/5 from 44,586 reviews (September 2026). Loox: 4.9/5 from 9,518 reviews. Judge.me has both a higher rating and a larger review base. Both apps hold the "Built for Shopify" badge. The volume difference is significant — Judge.me has 4.7x more reviews — and the rating gap, while small, is consistent.</div></div>
<div class="ex-card ex-card-neutral"><b>Do both apps support Google Shopping and rich snippets?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Yes. Both generate Google rich snippets (star ratings in search results) and syndicate reviews to Google Shopping. Judge.me includes Google Shopping syndication on the Awesome plan ($15/month). Loox includes Google Shopping on the Convert plan ($49.99/month). Both also support Meta Shops and TikTok Shop syndication on their paid plans.</div></div>
<div class="ex-card ex-card-neutral"><b>Which app is better for agencies managing multiple Shopify stores?</b><div style="margin-top:8px; font-size:14px; line-height:1.6;">Judge.me is the more经济 choice for agencies: each store pays a flat $15/month regardless of volume, so managing 10 stores costs $150/month total. Loox's usage-based pricing means each store's cost depends on its order volume, making budgeting unpredictable across a portfolio. Judge.me also offers a partner program with potential discounts for agencies.</div></div>

<h2 id="verdict">🔎 Final verdict</h2>
<div class="ex-summary-bg">
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:16px;">
<div class="ex-summary-card" style="border-left:4px solid #10b981;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">🏆 Judge.me — 9.4</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The value king: unlimited reviews, unlimited requests, photo/video, AI features, referrals and Google Shopping for $15/month flat. No order caps, no usage fees, no surprises.</div><div class="ex-inner-green">The default recommendation for most Shopify stores.</div></div>
<div class="ex-summary-card" style="border-left:4px solid #f59e0b;"><div style="font-size:18px; font-weight:700; margin-bottom:8px;">📸 Loox — 8.2</div><div style="font-size:14px; line-height:1.6; margin-bottom:10px;">The visual specialist: 17+ polished widgets, AI Review Stories, Theme Matcher, referrals and upsells — but usage-based pricing that scales unpredictably with order volume.</div><div class="ex-inner-orange">Worth it only when visual presentation and conversion tools justify the cost premium.</div></div>
</div>
</div>

<div class="ex-card ex-card-green-strong"><div style="font-weight:700;">The honest summary: <b>Judge.me wins on value, security and predictability.</b> At $15/month with unlimited everything, no order caps, ISO 27001 and SOC 2 certifications, and a 5.0-star rating from 44,000+ reviews, it is the objectively safer choice for most Shopify stores. <b>Loox wins on visual quality and conversion depth</b> — its AI Review Stories, Theme Matcher and triple-referral system produce a more polished and feature-rich experience, but at a price that scales with your success and carries billing surprises that Judge.me's flat model eliminates. The right app is the one whose pricing model matches your growth stage: Judge.me for everyone, Loox for stores where visual presentation directly drives revenue and the cost premium is justified by measurable conversion lift.</div></div>

<div style="background:#0f172a; color:#fff; padding:22px; border-radius:14px; text-align:center;">
<div style="font-size:24px; font-weight:700; margin-bottom:12px;">Reviews are infrastructure, not decoration</div>
<div style="font-size:16px; line-height:1.6; opacity:0.95; max-width:720px; margin:0 auto;">
The best review app is the one that keeps collecting while you sleep, displays trust signals where shoppers decide, and does not surprise you at billing time. On Shopify, that usually means Judge.me — unless your brand's visual identity demands Loox's widget quality.
<div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,0.2);">Explore the tool cards above — both apps have full profiles with criteria scores on this site.</div>
</div>
</div>
HTML;
}
