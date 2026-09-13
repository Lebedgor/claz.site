<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Seeder;

class PhotoReviewsCommentsSeeder extends Seeder
{
    public function run(): void
    {
        $article = Article::query()->where('slug->en', 'best-woocommerce-photo-review-plugins')->first();

        if ($article === null) {
            $this->command->warn('Article not found — run WooCommercePhotoReviewsSeeder first.');

            return;
        }

        $comments = [
            [
                'name' => 'Elena Voronova',
                'email' => 'elena@potterybarnco.example',
                'days_ago' => 5,
                'body' => '<p>One detail worth adding about CusRev: the coupon engine supports conditional rules, but the <strong>minimum order amount filter is per-coupon, not per-review-type</strong>. So if you set a 10% coupon for any review and a 15% coupon for photo reviews, both coupons can stack if a customer leaves two separate reviews. You need to set "single use per email" on both coupons and make sure the photo coupon has a higher minimum order amount to avoid accidental double-dipping. Learned this the hard way last month.</p>',
            ],
            [
                'name' => 'Marcus Chen',
                'email' => 'marcus@techgadgets.example',
                'days_ago' => 4,
                'body' => '<p>Switched from Judge.me to Yuko last week after the sunset email. The one-click import worked as advertised — all 1,200 reviews with photos came through. But one thing the article should mention: <strong>Yuko\'s free plan caps at 50 orders/month</strong>, and that includes orders from months ago if you are importing. We had to jump to the $12 Basic plan immediately because the import counted against the free tier. Not a dealbreaker, but plan for it.</p>',
            ],
            [
                'name' => 'Sarah Mitchell',
                'email' => 'sarah@craftsupplies.example',
                'days_ago' => 3,
                'body' => '<p>Running the official WooCommerce Marketplace plugin on two stores. The image cropping is genuinely useful — our product photos used to look inconsistent because customers uploaded wide landscape shots next to square close-ups. The 1:1 crop ratio fixed that overnight. One limitation: <strong>the plugin does not support video reviews</strong>, only photos. If you sell products where video demos matter (electronics, tools, kitchenware), you will need to pair it with CusRev for video or look at Yuko.</p>',
            ],
            [
                'name' => 'David Park',
                'email' => 'david@outdoorgear.example',
                'days_ago' => 2,
                'body' => '<p>The ReviewX security note is not theoretical — we got hit by CVE-2026-57359 before patching. The vulnerability was exploitable via the review form on the front end, and we found injected scripts in three review submissions before our security scanner caught it. <strong>Update to 2.3.11 immediately if you run ReviewX</strong>. The patch is clean, but the fact that the REST endpoint accepted unauthenticated POST requests for months is concerning. For stores that cannot afford downtime, CusRev or the Marketplace plugin are safer bets from a security posture standpoint.</p>',
            ],
            [
                'name' => 'Anna Kowalski',
                'email' => 'anna@homedecor.example',
                'days_ago' => 1,
                'body' => '<p>Worth noting that VillaTheme\'s AliExpress import (premium) has a <strong>hard limit of 500 reviews per import</strong>, and the translated-to-English option sometimes produces awkward phrasing that looks spammy to shoppers. We import 100–150 reviews per product with the "include pictures only" filter and then hand-edit the top 10. The time investment pays off — products with curated imported photo reviews convert noticeably better than products with only text reviews from our own customers.</p>',
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
