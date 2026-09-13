<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Seeder;

class ElementorVsBricksCommentsSeeder extends Seeder
{
    public function run(): void
    {
        $article = Article::query()->where('slug->en', 'elementor-vs-bricks')->first();

        if ($article === null) {
            $this->command->warn('Article not found — run ElementorVsBricksSeeder first.');

            return;
        }

        $comments = [
            [
                'name' => 'Thomas Eriksen',
                'email' => 'thomas@nordicstudio.example',
                'days_ago' => 5,
                'body' => '<p>We migrated 12 client sites from Elementor to Bricks over the past six months. The rebuild time averaged 3–4 days per site (from a fully built Elementor site to an equivalent Bricks build). The performance improvement was consistent: <strong>LCP dropped by 40–60% on every site</strong>, Lighthouse scores went from 70s to 95+, and client feedback on page speed has been overwhelmingly positive. The $599 lifetime license paid for itself after the third site migration. The learning curve is real — our junior designer needed about two weeks to feel comfortable — but the output quality is worth it.</p>',
            ],
            [
                'name' => 'Sarah Mitchell',
                'email' => 'sarah@craftsbysarah.example',
                'days_ago' => 4,
                'body' => '<p>I tried Bricks for a month and went back to Elementor. The CSS class system is powerful, but I build sites for clients who need to edit content themselves, and <strong>Elementor\'s visual interface is simply easier for non-technical users to understand</strong>. My clients can add a new section, change colors and rearrange widgets without calling me. With Bricks, they would need to understand classes and breakpoints. For agencies that hand off sites to clients, Elementor\'s ease of use is a real business advantage — even if the code output is heavier.</p>',
            ],
            [
                'name' => 'Marcus Rivera',
                'email' => 'marcus@performancewp.example',
                'days_ago' => 3,
                'body' => '<p>The performance comparison in the article understates the gap. We ran identical test pages — same content, same images, same hosting — and measured: Bricks produced a 38KB page weight versus Elementor\'s 127KB. That is a <strong>3.3x difference</strong> before any optimization. On shared hosting where every kilobyte matters for TTFB, this is not a rounding error — it is the difference between a green and yellow Core Web Vitals score. Editor V4 helps, but it does not close a 3x gap.</p>',
            ],
            [
                'name' => 'Anika Patel',
                'email' => 'anika@webagency.example',
                'days_ago' => 2,
                'body' => '<p>One thing the article does not mention: <strong>Bricks\' Gutenberg block rendering is a game-changer for client handoff</strong>. You design the layout in Bricks, save it as a native WordPress block, and the client edits content through the standard block editor — no Bricks knowledge required. This splits the difference between "developer builds, client edits" in a way Elementor cannot replicate without using a separate plugin. Worth investigating if you build for clients who need ongoing content independence.</p>',
            ],
            [
                'name' => 'David Chen',
                'email' => 'david@saasfounder.example',
                'days_ago' => 1,
                'body' => '<p>Honest question for Bricks users: how do you handle the "no free trial" barrier when pitching to clients? With Elementor, I can show a client the free editor in 5 minutes and they immediately understand the value proposition. With Bricks, I have to explain that it costs $79 upfront before they can even see the interface. <strong>The 60-day money-back guarantee helps, but the psychological barrier of "pay before you try" is real</strong> when you are selling a $5,000 website project.</p>',
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
