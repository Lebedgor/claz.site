<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Seeder;

class YoastVsRankMathCommentsSeeder extends Seeder
{
    public function run(): void
    {
        $article = Article::query()->where('slug->en', 'yoast-seo-vs-rank-math')->first();

        if ($article === null) {
            $this->command->warn('Article not found — run YoastVsRankMathSeeder first.');

            return;
        }

        $comments = [
            [
                'name' => 'Tomas Reinhardt',
                'email' => 'tomas@studio-nord.example',
                'days_ago' => 6,
                'body' => '<p>Practical question for anyone who has done the migration: the Rank Math importer copies titles and meta descriptions, but what happens to <strong>redirects created in Yoast Premium</strong>? We manage around 40 client sites and most have hundreds of legacy redirects. Does the import cover them, or do we need to export the redirect table separately and bring it in via CSV? We can tolerate redoing schema, but silently losing 301s on client domains would be a disaster.</p>',
            ],
            [
                'name' => 'Grace Whitfield',
                'email' => 'grace@smallpress.example',
                'days_ago' => 4,
                'body' => '<p>The per-site detail in the pricing section is the thing most comparisons miss. We publish three niche magazines on separate domains and were quoted by Yoast — Premium for each site. With Rank Math PRO that is one subscription covering all three plus our two sister blogs. Before committing I checked the fine print: unlimited personal websites, client sites excluded. For a small publisher that distinction matters, so read the license terms before buying either way.</p>',
            ],
            [
                'name' => 'Devon Okafor',
                'email' => 'devon@growthlab.example',
                'days_ago' => 2,
                'body' => '<p>Question about Content AI for the author wondering about AI features: is the credit system workable for a solo blogger, or does it push you toward a bigger plan quickly? I like that Rank Math includes a 15-day trial on PRO, but I cannot find a clear answer on how many credits a typical "write an outline plus a meta description per week" workflow actually burns. Yoast\'s bundled AI (titles and descriptions) looks cheaper for that narrow use case, even at $118.80/year.</p>',
            ],
            [
                'name' => 'Ines Moreau',
                'email' => 'ines@agence-web.example',
                'days_ago' => 1,
                'body' => '<p>One edge case worth adding about Rank Math on shared hosting: on a client site with roughly 30,000 posts, the analytics module (Search Console sync) made the admin dashboard noticeably sluggish until we limited the data fetch frequency and preserved-days settings. The frontend was fine — it is purely an admin-area issue. On normal-sized sites this never shows up, but if you run a large archive on cheap hosting, tune those two settings right after activating the module.</p>',
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
