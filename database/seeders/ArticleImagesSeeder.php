<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleImagesSeeder extends Seeder
{
    public function run(): void
    {
        $updates = [
            'judge-me-vs-loox' => [
                ['after' => '<h2 id="collection">', 'html' => '
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/judge-me/review-widget.jpg" alt="Judge.me review widget displayed on a product page" loading="lazy"><figcaption>Judge.me review widget: unlimited reviews on every plan, including the free tier. Screenshot: Judge.me.</figcaption></figure>
<figure><img src="/storage/uploads/article/apps/loox/photo-grid-hero.png" alt="Loox photo review grid widget with customer images" loading="lazy"><figcaption>Loox photo review grid: visual-first display with 17+ widget options. Screenshot: Loox.</figcaption></figure>
</div>'],
                ['after' => '<h2 id="display">', 'html' => '
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/judge-me/widgets.png" alt="Judge.me widget customization options" loading="lazy"><figcaption>Judge.me widgets: functional, fast-loading, fully customizable. Screenshot: Judge.me.</figcaption></figure>
<figure><img src="/storage/uploads/article/apps/loox/reviews-widgets.png" alt="Loox review widgets overview with multiple display formats" loading="lazy"><figcaption>Loox widgets: 17+ conversion-optimized display formats. Screenshot: Loox.</figcaption></figure>
</div>'],
                ['after' => '<h2 id="conversion">', 'html' => '
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/loox/ai-summaries.png" alt="Loox AI Review Stories and Smart Sorting features" loading="lazy"><figcaption>Loox AI features: Smart Sorting, Review Stories and AI Summaries — gated behind the $49.99 Convert plan. Screenshot: Loox.</figcaption></figure>
</div>'],
            ],
            'elementor-vs-bricks' => [
                ['after' => '<h2 id="philosophy">', 'html' => '
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/elementor/editor-v4.png" alt="Elementor Editor V4 (Atomic) with CSS-first foundation" loading="lazy"><figcaption>Elementor Editor V4 (Atomic): the 2026 CSS-first update reduces nested divs and improves TTFB. Screenshot: Elementor.</figcaption></figure>
<figure><img src="/storage/uploads/article/apps/bricks/editor-hero.png" alt="Bricks Builder editor interface with Vue.js-powered canvas" loading="lazy"><figcaption>Bricks Builder: Vue.js-powered editor with clean code output and CSS class management. Screenshot: Bricks.</figcaption></figure>
</div>'],
                ['after' => '<h2 id="features">', 'html' => '
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/elementor/theme-builder.png" alt="Elementor Theme Builder for headers, footers and templates" loading="lazy"><figcaption>Elementor Theme Builder: visual control over headers, footers, archives and single templates. Screenshot: Elementor.</figcaption></figure>
<figure><img src="/storage/uploads/article/apps/elementor/widgets.png" alt="Elementor Pro widget library with 85+ widgets" loading="lazy"><figcaption>Elementor Pro: 85+ widgets including WooCommerce, forms, popups and motion effects. Screenshot: Elementor.</figcaption></figure>
</div>'],
                ['after' => '<h2 id="dev">', 'html' => '
<div class="images-block">
<figure><img src="/storage/uploads/article/apps/bricks/features-overview.png" alt="Bricks Builder design sets and wireframe templates" loading="lazy"><figcaption>Bricks Builder: pre-made wireframes and design sets available on every plan. Screenshot: Bricks.</figcaption></figure>
</div>'],
            ],
        ];

        foreach ($updates as $slug => $inserts) {
            $article = Article::where('slug->en', $slug)->first();

            if ($article === null) {
                $this->command->warn("Article not found: {$slug}");

                continue;
            }

            $body = $article->getTranslation('body_html', 'en');

            usort($inserts, fn ($a, $b) => strrpos($body, $b['after']) <=> strrpos($body, $a['after']));

            foreach ($inserts as $insert) {
                $pos = strrpos($body, $insert['after']);

                if ($pos === false) {
                    $this->command->warn("Marker not found: {$insert['after']} in {$slug}");

                    continue;
                }

                $body = substr_replace($body, $insert['html'], $pos + strlen($insert['after']), 0);
            }

            $article->update(['body_html' => ['en' => $body]]);
            $this->command->info("Updated: {$slug}");
        }
    }
}
