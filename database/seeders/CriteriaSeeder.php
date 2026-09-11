<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            ['name' => 'Ease of use', 'kind' => 'score', 'weight' => 3, 'sort_order' => 10],
            ['name' => 'Features', 'kind' => 'score', 'weight' => 3, 'sort_order' => 20],
            ['name' => 'Performance', 'kind' => 'score', 'weight' => 2, 'sort_order' => 30],
            ['name' => 'Pricing', 'kind' => 'score', 'weight' => 2, 'sort_order' => 40],
            ['name' => 'Support & docs', 'kind' => 'score', 'weight' => 1, 'sort_order' => 50],
            ['name' => 'Free plan', 'kind' => 'bool', 'weight' => 1, 'sort_order' => 60],
            ['name' => 'Open source', 'kind' => 'bool', 'weight' => 1, 'sort_order' => 70],
        ];

        foreach ($criteria as $criterion) {
            DB::table('criteria')->insert([
                'name' => json_encode(['en' => $criterion['name']]),
                'kind' => $criterion['kind'],
                'weight' => $criterion['weight'],
                'sort_order' => $criterion['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
