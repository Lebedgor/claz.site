<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Seeder;

class ArticleCommentsSeeder extends Seeder
{
    public function run(): void
    {
        $article = Article::query()->where('slug->en', 'best-product-review-apps-for-shopify')->first();

        if ($article === null) {
            $this->command->warn('Article not found — run ShopifyReviewsSeeder first.');

            return;
        }

        $comments = [
            [
                'name' => 'Martin Kovač',
                'email' => 'martin@alpinewatches.example',
                'days_ago' => 6,
                'body' => '<p>Useful breakdown — one important SEO nuance you hinted at but worth spelling out. Since Google\'s 2019 policy update, self-serving review rich snippets are ignored for Organization and LocalBusiness markup, but <strong>Product-schema review stars on product pages still show</strong>. So review stars in organic results are absolutely still a thing for e-commerce — just don\'t expect stars on your homepage or about page.</p><p>Also, in our experience Judge.me widgets keep Core Web Vitals green even past 5,000 reviews per product, but make sure you enable lazy loading and keep the all-reviews page paginated.</p>',
            ],
            [
                'name' => 'Dana Whitfield',
                'email' => 'dana@everbloomshop.example',
                'days_ago' => 5,
                'body' => '<p>Running a beauty brand on Loox here. One thing the article doesn\'t mention: most of these apps give <strong>two months free on annual billing</strong>, and vendors quietly push monthly plans because churn is higher. Before you pick a tier, ask for the annual price — on Loox Convert that is roughly $500 saved per year, and the same logic applies to Okendo and Stamped.</p><p>Also worth knowing: Loox\'s review-request emails support custom sender names and logos on every plan, which measurably lifted our submission rate versus the default branding.</p>',
            ],
            [
                'name' => 'Priya Nair',
                'email' => 'priya@urbanrootsgear.example',
                'days_ago' => 4,
                'body' => '<p>Addition on Okendo from real usage: the underrated feature is not the reviews themselves but <strong>UGC rights management</strong>. When customers submit photos, Okendo tracks consent so you can legally reuse those images in ads, emails and product pages — something Judge.me and Loox handle much more crudely. If you plan to run your customers\' photos as ad creative, factor that into the Okendo vs Loox decision.</p><p>Their quizzes product is also genuinely useful for skincare and supplements — it feeds structured data back into the same customer profile as the reviews.</p>',
            ],
            [
                'name' => 'Tomas Berg',
                'email' => 'tomas@nordicsled.example',
                'days_ago' => 2,
                'body' => '<p>A caveat from the headless corner for anyone considering Junip: the API access is locked behind the $299 Premium plan, so if you run a Hydrogen or custom storefront, the lower tiers are widget-embed only. We ended up on Premium and the API is solid, but budget for it — on Judge.me the equivalent JSON endpoints are available on the $15 Awesome plan, which stings a bit when comparing the two.</p><p>That said, Junip\'s submission forms really do convert better on mobile — our completion rate went up by roughly a third after switching the collection flow to them.</p>',
            ],
            [
                'name' => 'Elif Demir',
                'email' => 'elif@zenmartshop.example',
                'days_ago' => 1,
                'body' => '<p>One practical warning about Ali Reviews from running a dropshipping store for two years: curate aggressively. Importing the default 50–150 reviews per product looks spammy and Tank_conversion — the sweet spot is 15–30 well-rated imported reviews per product, with the obviously fake ones (identical wording, five-star-only profiles) filtered out. Gemini translation is good, but for your top two markets it pays to hand-polish the top 5 reviews.</p><p>And agree with the article on the migration path: we moved our curated reviews to Judge.me with the CSV import in under an hour, and Google re-indexed the star ratings within two weeks.</p>',
            ],
        ];

        foreach ($comments as $data) {
            $existing = Comment::query()
                ->where('article_id', $article->getKey())
                ->where('email', $data['email'])
                ->first();

            if ($existing !== null) {
                continue;
            }

            Comment::create([
                'article_id' => $article->getKey(),
                'name' => $data['name'],
                'email' => $data['email'],
                'body' => HtmlSanitizer::clean($data['body']),
                'status' => CommentStatus::Approved,
                'ip_hash' => null,
                'created_at' => now()->subDays($data['days_ago']),
                'updated_at' => now()->subDays($data['days_ago']),
            ]);
        }
    }
}
