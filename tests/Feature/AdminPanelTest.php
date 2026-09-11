<?php

use App\Enums\CriterionKind;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Criterion;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renders admin resources for the admin', function () {
    actingAs(User::factory()->create());

    $this->get('/admin')->assertOk();
    $this->get('/admin/tools')->assertOk();
    $this->get('/admin/tools/create')->assertOk();
    $this->get('/admin/criteria')->assertOk();
    $this->get('/admin/categories')->assertOk();
    $this->get('/admin/tags')->assertOk();
});

it('renders tool edit page with attached criteria values', function () {
    actingAs(User::factory()->create());

    $tool = Tool::create([
        'type' => ToolType::Plugin,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Yoast SEO'],
        'slug' => ['en' => 'yoast-seo'],
        'vendor' => 'Yoast',
    ]);

    $criterion = Criterion::create([
        'name' => ['en' => 'Ease of use'],
        'kind' => CriterionKind::Score,
    ]);

    $tool->criteria()->attach($criterion->getKey(), ['value' => 8]);

    $this->get('/admin/tools/'.$tool->getKey().'/edit')->assertOk();
    $this->get('/admin/criteria/'.$criterion->getKey().'/edit')->assertOk();
});
