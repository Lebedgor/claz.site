<?php

namespace Database\Seeders;

use App\Actions\SyncComparisonScores;
use App\Enums\ArticleStatus;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Criterion;
use App\Models\Tool;
use App\Models\ToolLink;
use App\Support\ReadingTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::query()->where('slug->en', 'chatgpt-vs-claude')->exists()) {
            $this->command->info('Demo articles already exist — skipped to preserve admin edits.');

            return;
        }

        $aiCategory = Category::query()->where('slug->en', 'ai-tools')->first()
            ?? Category::create([
                'name' => ['en' => 'AI tools'],
                'slug' => ['en' => 'ai-tools'],
                'description' => ['en' => 'Chatbots, generators and AI assistants compared.'],
                'sort_order' => 10,
            ]);

        $seoCategory = Category::query()->where('slug->en', 'seo-marketing')->first()
            ?? Category::create([
                'name' => ['en' => 'SEO & marketing'],
                'slug' => ['en' => 'seo-marketing'],
                'description' => ['en' => 'Plugins and services for search optimization.'],
                'sort_order' => 20,
            ]);

        $toolData = [
            'ChatGPT' => [
                'type' => ToolType::Ai, 'vendor' => 'OpenAI', 'rating' => 9.1,
                'description' => 'The most popular general-purpose AI chatbot with strong writing and reasoning skills.',
                'affiliate' => true,
                'values' => ['Ease of use' => 9, 'Features' => 9, 'Performance' => 8, 'Pricing' => 7, 'Support & docs' => 8, 'Free plan' => true],
            ],
            'Claude' => [
                'type' => ToolType::Ai, 'vendor' => 'Anthropic', 'rating' => 8.7,
                'description' => 'A thoughtful AI assistant known for long-context analysis and natural writing.',
                'affiliate' => false,
                'values' => ['Ease of use' => 8, 'Features' => 8, 'Performance' => 9, 'Pricing' => 7, 'Support & docs' => 7, 'Free plan' => true],
            ],
            'Yoast SEO' => [
                'type' => ToolType::Plugin, 'vendor' => 'Yoast', 'rating' => 8.2,
                'description' => 'The classic WordPress SEO plugin with content analysis and schema support.',
                'affiliate' => true,
                'values' => ['Ease of use' => 8, 'Features' => 8, 'Performance' => 7, 'Pricing' => 6, 'Support & docs' => 9, 'Free plan' => true, 'Open source' => true],
            ],
            'Ahrefs' => [
                'type' => ToolType::Service, 'vendor' => 'Ahrefs', 'rating' => 9.0,
                'description' => 'A leading SEO toolkit with the best backlink index on the market.',
                'affiliate' => false,
                'values' => ['Ease of use' => 7, 'Features' => 10, 'Performance' => 9, 'Pricing' => 5, 'Support & docs' => 8, 'Free plan' => false],
            ],
        ];

        $tools = [];

        foreach ($toolData as $name => $data) {
            $slug = Str::slug($name);

            $tool = Tool::create([
                'type' => $data['type'],
                'status' => ToolStatus::Published->value,
                'name' => ['en' => $name],
                'slug' => ['en' => $slug],
                'vendor' => $data['vendor'],
                'description' => ['en' => $data['description']],
                'rating_avg' => $data['rating'],
            ]);

            foreach ($data['values'] as $criterionName => $value) {
                $criterion = Criterion::query()->where('name->en', $criterionName)->first();

                if ($criterion !== null) {
                    $tool->criteria()->attach($criterion->getKey(), ['value' => $value]);
                }
            }

            ToolLink::create([
                'tool_id' => $tool->getKey(),
                'code' => $slug,
                'url' => 'https://example.com/'.$slug,
                'anchor' => ['en' => 'Visit '.$name],
                'is_affiliate' => $data['affiliate'],
            ]);

            $tools[$name] = $tool;
        }

        $article = Article::create([
            'category_id' => $aiCategory->getKey(),
            'title' => ['en' => 'ChatGPT vs Claude: which AI chatbot to pick in 2026'],
            'slug' => ['en' => 'chatgpt-vs-claude'],
            'excerpt' => ['en' => 'We used both assistants for a month of real work: writing, coding, analysis. Here is how they compare on features, quality and price.'],
            'body_html' => ['en' => '<p>ChatGPT and Claude are the two most capable general-purpose assistants today. We spent a month using both for real work: drafting articles, analyzing documents and writing code.</p><h2>How we tested</h2><p>Each assistant handled the same set of tasks. We scored them on ease of use, feature depth, output quality and price.</p><p>[[comparison:COMPARISON_ID]]</p><h2>Bottom line</h2><p>Pick ChatGPT if you want the broadest ecosystem and plugin support. Pick Claude if your work involves long documents and you value careful, natural writing.</p>'],
            'status' => ArticleStatus::Published->value,
            'published_at' => now()->subDays(3),
            'meta_title' => ['en' => 'ChatGPT vs Claude 2026 comparison'],
            'meta_description' => ['en' => 'A month of real-world testing: how ChatGPT and Claude compare on features, quality and price.'],
        ]);

        $comparison = $article->comparisons()->create([
            'title' => ['en' => 'ChatGPT vs Claude head to head'],
            'intro' => ['en' => 'Criteria scores from our month of testing.'],
            'verdict' => ['en' => 'Both are excellent; the choice depends on your workflow.'],
            'sort_order' => 0,
        ]);

        $comparison->items()->create([
            'tool_id' => $tools['ChatGPT']->getKey(),
            'position' => 0,
            'score' => 8.6,
            'verdict' => ['en' => 'Best all-rounder'],
        ]);

        $comparison->items()->create([
            'tool_id' => $tools['Claude']->getKey(),
            'position' => 1,
            'score' => 8.1,
            'verdict' => ['en' => 'Best for long documents'],
        ]);

        app(SyncComparisonScores::class)->execute($article->refresh());

        $article->update([
            'body_html' => ['en' => str_replace('COMPARISON_ID', (string) $comparison->getKey(), strval($article->getTranslation('body_html', 'en')))],
            'reading_time' => ReadingTime::estimate($article->getTranslation('body_html', 'en')),
        ]);

        Article::create([
            'category_id' => $seoCategory->getKey(),
            'title' => ['en' => 'Yoast SEO vs Ahrefs: plugin or full toolkit?'],
            'slug' => ['en' => 'yoast-vs-ahrefs'],
            'excerpt' => ['en' => 'One is a WordPress plugin, the other a full SEO platform. Which one does your site actually need?'],
            'body_html' => ['en' => '<p>Yoast SEO and Ahrefs solve different problems, but buyers often compare them. Here is what each is actually good at.</p>'],
            'status' => ArticleStatus::Published->value,
            'published_at' => now()->subDay(),
            'meta_title' => ['en' => 'Yoast SEO vs Ahrefs'],
            'meta_description' => ['en' => 'Plugin or platform? We break down when Yoast is enough and when you need Ahrefs.'],
            'reading_time' => 4,
        ]);
    }
}
