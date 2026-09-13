<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Seeder;

class WordPressPageBuildersCommentsSeeder extends Seeder
{
    public function run(): void
    {
        $article = Article::query()->where('slug->en', 'best-wordpress-page-builders')->first();

        if ($article === null) {
            $this->command->warn('Article not found — run WordPressPageBuildersSeeder first.');

            return;
        }

        $comments = [
            [
                'name' => 'Daniel Fischer',
                'email' => 'daniel@webwerkstatt.example',
                'days_ago' => 5,
                'body' => '<p>One migration detail worth adding: when moving from Elementor to Bricks, the biggest headache is not the layout — it is <strong>Dynamic Content</strong>. Elementor stores ACF and custom field connections in its own JSON format, and Bricks uses a completely different query system. Budget a full day per page for complex dynamic templates, not the 15 minutes the migration guides suggest.</p><p>That said, the end result is worth it — our Elementor site went from a 72 mobile PageSpeed to 96 on the same hosting after rebuilding in Bricks, with zero caching plugins.</p>',
            ],
            [
                'name' => 'Sarah Mitchell',
                'email' => 'sarah@brightpathagency.example',
                'days_ago' => 4,
                'body' => '<p>Agency perspective on the Divi lifetime deal: it is genuinely the best value if you build 15+ sites per year, but factor in the <strong>client handoff cost</strong>. Divi\'s interface is less intuitive for non-technical clients than Elementor\'s, and we spend roughly 30 extra minutes per client training them on Divi versus Elementor. At 20 clients per year that is 10 hours of unpaid training — still worth it for the license savings, but not free.</p><p>We switched our performance-critical clients to Bricks last year and the maintenance tickets dropped by about 40% — fewer "my site is slow" complaints.</p>',
            ],
            [
                'name' => 'Yuri Petrov',
                'email' => 'yuri@fastwpstudio.example',
                'days_ago' => 3,
                'body' => '<p>Breakdance deserves more attention for WooCommerce specifically. We migrated three stores from Elementor and the <strong>cart and checkout builders</strong> alone saved us from needing WooCommerce Cart Flows or a similar plugin. The global WooCommerce styles mean you can change your sale badge color, product card layout and checkout field styling from one panel — something Elementor requires Custom CSS or an addon for.</p><p>The one downside: Breakdance\'s popup builder conditions are less granular than Elementor\'s. We had to use a lightweight custom plugin for one client\'s complex exit-intent logic.</p>',
            ],
            [
                'name' => 'Lisa Tanaka',
                'email' => 'lisa@craftedsites.example',
                'days_ago' => 2,
                'body' => '<p>For anyone on the fence: the <strong>free tier comparison</strong> matters more than people think. Elementor Free gives you 57 widgets and basic theme builder — enough for most brochure sites. Breakdance Free gives you 80 elements and unlimited sites, which is even more generous. Bricks and Divi have no free version, so you are paying $79 or $89 before you can evaluate them properly.</p><p>We start every new project on Breakdance Free now. If the project needs advanced WooCommerce or dynamic queries, we upgrade to Pro. If it is a simple site, the free tier is genuinely enough.</p>',
            ],
            [
                'name' => 'Marcus Weber',
                'email' => 'marcus@performancepress.example',
                'days_ago' => 1,
                'body' => '<p>The Core Web Vitals numbers in the article are accurate from our testing too, but one nuance: <strong>Elementor V4 changes the equation significantly</strong>. We enabled it on three client sites and saw DOM reduction of ~20% and CSS reduction of ~50% compared to V3. The gap between Elementor V4 and Bricks is real but smaller than the article suggests — Elementor V4 hits 82–88 mobile PageSpeed on our test pages, versus 93–95 for Bricks.</p><p>The catch: V4 is still alpha, not all widgets are migrated, and the addon ecosystem is not ready for it yet. Give it 6 months.</p>',
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
