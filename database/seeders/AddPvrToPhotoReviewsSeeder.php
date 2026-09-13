<?php

namespace Database\Seeders;

use App\Actions\SyncComparisonScores;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Article;
use App\Models\Criterion;
use App\Models\Tool;
use App\Models\ToolLink;
use Illuminate\Database\Seeder;

class AddPvrToPhotoReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $toolData = [
            'type' => ToolType::Plugin,
            'vendor' => 'PVR',
            'rating' => 9.5,
            'description' => 'A self-hosted photo and video review plugin that compresses videos in the customer browser before upload — no FFmpeg, no server CPU load. Native WooCommerce review storage, built-in rich snippets, helpful voting, store replies, an SEO-first Pro tier with a reviews hub page, and a founding-customer lifetime license at $59.',
            'url' => 'https://wordpress.org/plugins/pvr-media-reviews-for-woocommerce/',
            'values' => ['Ease of use' => 9, 'Features' => 10, 'Performance' => 10, 'Pricing' => 9, 'Support & docs' => 7, 'Free plan' => true, 'Open source' => true],
        ];

        $tool = Tool::query()->where('slug->en', 'pvr-media-reviews')->first();

        if ($tool === null) {
            $tool = Tool::create([
                'type' => $toolData['type'],
                'status' => ToolStatus::Published->value,
                'name' => ['en' => 'PVR Media Reviews'],
                'slug' => ['en' => 'pvr-media-reviews'],
                'vendor' => $toolData['vendor'],
                'description' => ['en' => $toolData['description']],
                'rating_avg' => $toolData['rating'],
            ]);
        }

        $criteria = [];
        foreach (Criterion::query()->orderBy('sort_order')->get() as $criterion) {
            $criteria[$criterion->getTranslation('name', 'en')] = $criterion;
        }

        foreach ($toolData['values'] as $criterionName => $value) {
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
                'code' => 'pvr-media-reviews',
                'url' => $toolData['url'],
                'anchor' => ['en' => 'Visit PVR Media Reviews'],
                'is_affiliate' => false,
            ]);
        }

        $article = Article::query()->where('slug->en', 'best-woocommerce-photo-review-plugins')->first();

        if ($article === null) {
            $this->command->warn('Article not found — run WooCommercePhotoReviewsSeeder first (it already includes PVR).');

            return;
        }

        $comparison = $article->comparisons()->first();

        if ($comparison === null) {
            $this->command->warn('Comparison not found.');

            return;
        }

        $existingItem = $comparison->items()->where('tool_id', $tool->getKey())->first();

        if ($existingItem === null) {
            foreach ($comparison->items()->orderBy('position')->get() as $item) {
                $item->update(['position' => $item->position + 1]);
            }

            $comparison->items()->create([
                'tool_id' => $tool->getKey(),
                'position' => 0,
                'score' => 9.5,
                'verdict' => ['en' => 'Best overall — photos and videos on your own site, a reviews hub for Google, $59 once'],
            ]);

            app(SyncComparisonScores::class)->execute($article->refresh());
        }

        $this->command->info('PVR tool and comparison item ensured (article body is managed by WooCommercePhotoReviewsSeeder).');
    }
}
