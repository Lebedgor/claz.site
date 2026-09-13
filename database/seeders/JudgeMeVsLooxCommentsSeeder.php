<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Seeder;

class JudgeMeVsLooxCommentsSeeder extends Seeder
{
    public function run(): void
    {
        $article = Article::query()->where('slug->en', 'judge-me-vs-loox')->first();

        if ($article === null) {
            $this->command->warn('Article not found — run JudgeMeVsLooxSeeder first.');

            return;
        }

        $comments = [
            [
                'name' => 'Raj Patel',
                'email' => 'raj@techstore.example',
                'days_ago' => 5,
                'body' => '<p>Switched from Loox to Judge.me three months ago after our Convert plan hit $340/month on 2,100 orders. The billing surprise was the final straw — we had marketplace orders from Amazon and eBay counting toward our Loox limit even though Loox never touched those reviews. On Judge.me we pay $15/month flat and the review collection is honestly just as good. The only thing I genuinely miss is Loox\'s carousel widget — it was prettier. But prettier does not cost $325/month extra.</p>',
            ],
            [
                'name' => 'Emma Lindqvist',
                'email' => 'emma@scandinavianstyle.example',
                'days_ago' => 4,
                'body' => '<p>We stayed on Loox specifically because of the <strong>AI Review Stories</strong> widget. Our product page conversion rate went up roughly 12% after we enabled it — the swipeable format keeps shoppers engaged longer than Judge.me\'s standard grid. At our volume (about 400 orders/month), the Convert plan costs us $49.99, which is manageable. But I agree with the article: if we were processing 2,000+ orders, the math would not work. Know your numbers before choosing.</p>',
            ],
            [
                'name' => 'Marcus Thompson',
                'email' => 'marcus@outdoorco.example',
                'days_ago' => 3,
                'body' => '<p>The security comparison is underrated. We went through a SOC 2 audit for our enterprise B2B channel and our auditor specifically asked about third-party app data handling. Judge.me\'s ISO 27001 and SOC 2 Type 2 certifications made that conversation easy. Loox could not provide equivalent documentation when we asked. For DTC-only stores this probably does not matter, but if you sell to businesses or handle regulated data, Judge.me\'s security posture is a real differentiator.</p>',
            ],
            [
                'name' => 'Sophie Moreau',
                'email' => 'sophie@beautybrand.example',
                'days_ago' => 2,
                'body' => '<p>One thing the article could expand on: Judge.me\'s <strong>video carousel widget</strong> is available on the free plan, while Loox locks video reviews behind the $49.99 Convert plan. For beauty brands where customers film application tutorials, that is a significant feature gap at the free tier. We collect about 30% video reviews and Judge.me handles them natively without any paid upgrade.</p>',
            ],
            [
                'name' => 'David Kim',
                'email' => 'david@minimalistgoods.example',
                'days_ago' => 1,
                'body' => '<p>Honest question for the Loox users here: has anyone successfully negotiated annual pricing? Their pricing page shows monthly only, and at $49.99/month that is $600/year versus Judge.me\'s $180/year. I asked Loox support about annual discounts and got a generic "contact sales" response. If anyone has gotten a meaningful annual discount from Loox, I would love to hear what terms you got.</p>',
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
