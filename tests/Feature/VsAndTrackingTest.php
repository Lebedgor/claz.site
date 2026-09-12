<?php

use App\Enums\CriterionKind;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Criterion;
use App\Models\Tool;
use App\Models\ToolLink;
use App\Models\VsPage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedVsFixture(): array
{
    $ease = Criterion::create([
        'name' => ['en' => 'Ease of use'],
        'kind' => CriterionKind::Score,
        'sort_order' => 10,
    ]);

    $free = Criterion::create([
        'name' => ['en' => 'Free plan'],
        'kind' => CriterionKind::Bool,
        'sort_order' => 60,
    ]);

    $alpha = Tool::create([
        'type' => ToolType::Ai,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Alpha AI'],
        'slug' => ['en' => 'alpha-ai'],
        'rating_avg' => 9.0,
    ]);

    $alpha->criteria()->attach([
        $ease->getKey() => ['value' => 9],
        $free->getKey() => ['value' => true],
    ]);

    $beta = Tool::create([
        'type' => ToolType::Ai,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Beta Bot'],
        'slug' => ['en' => 'beta-bot'],
        'rating_avg' => 7.5,
    ]);

    $beta->criteria()->attach([
        $ease->getKey() => ['value' => 6],
    ]);

    $link = ToolLink::create([
        'tool_id' => $alpha->getKey(),
        'code' => 'alpha',
        'url' => 'https://alpha.example.com',
        'is_affiliate' => true,
        'anchor' => ['en' => 'Try Alpha'],
    ]);

    $draft = Tool::create([
        'type' => ToolType::Service,
        'status' => ToolStatus::Draft,
        'name' => ['en' => 'Draft Tool'],
        'slug' => ['en' => 'draft-tool'],
    ]);

    return [$alpha, $beta, $link, $draft];
}

it('renders vs page with live criteria', function () {
    [$alpha, $beta] = seedVsFixture();

    $this->get('/vs/alpha-ai-vs-beta-bot')->assertOk()
        ->assertSee('Alpha AI vs Beta Bot')
        ->assertSee('Ease of use')
        ->assertSee('9')
        ->assertSee('6');
});

it('redirects reverse vs url to canonical', function () {
    seedVsFixture();

    $this->get('/vs/beta-bot-vs-alpha-ai')
        ->assertRedirect('http://127.0.0.1:8000/vs/alpha-ai-vs-beta-bot');
});

it('404s vs page when hidden by editorial row', function () {
    [$alpha, $beta] = seedVsFixture();

    VsPage::create([
        'tool_a_id' => $alpha->getKey(),
        'tool_b_id' => $beta->getKey(),
        'is_published' => false,
    ]);

    $this->get('/vs/alpha-ai-vs-beta-bot')->assertNotFound();
});

it('uses editorial intro and conclusion on vs page', function () {
    [$alpha, $beta] = seedVsFixture();

    VsPage::create([
        'tool_a_id' => $alpha->getKey(),
        'tool_b_id' => $beta->getKey(),
        'intro' => ['en' => 'Custom editorial intro'],
        'conclusion' => ['en' => 'Custom editorial conclusion'],
    ]);

    $this->get('/vs/alpha-ai-vs-beta-bot')->assertOk()
        ->assertSee('Custom editorial intro')
        ->assertSee('Custom editorial conclusion');
});

it('404s vs page for unknown or draft tools', function () {
    [$alpha, $beta, , $draft] = seedVsFixture();

    $this->get('/vs/alpha-ai-vs-unknown-tool')->assertNotFound();
    $this->get('/vs/alpha-ai-vs-draft-tool')->assertNotFound();
});

it('tracks click and redirects to affiliate url', function () {
    [$alpha, $beta, $link] = seedVsFixture();

    $this->get('/go/alpha')->assertRedirect('https://alpha.example.com');

    $this->assertDatabaseHas('click_events', [
        'tool_link_id' => $link->getKey(),
    ]);
});

it('404s on unknown go code', function () {
    seedVsFixture();

    $this->get('/go/unknown')->assertNotFound();
});
