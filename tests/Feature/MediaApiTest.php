<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function seedStorage(): void
{
    Storage::fake('public');
    Storage::fake('public')->deleteDirectory('uploads');
    Storage::disk('public')->put('uploads/article/featured.jpg', 'img');
    Storage::disk('public')->put('uploads/article/apps/shot.png', 'png');
    Storage::disk('public')->makeDirectory('uploads/article/empty-folder');
}

it('requires authentication for the media api', function () {
    $this->getJson('/admin/media/api')->assertUnauthorized();
});

it('lists a folder with files and subfolders', function () {
    actingAs(User::factory()->create());
    seedStorage();

    $response = $this->getJson('/admin/media/api?dir=uploads/article');

    $response->assertOk();
    expect($response->json('dir'))->toBe('uploads/article')
        ->and($response->json('folders'))->toContain('apps')
        ->and($response->json('folders'))->toContain('empty-folder')
        ->and($response->json('files.0.name'))->toBe('featured.jpg')
        ->and($response->json('files.0.rel'))->toStartWith('/storage/')
        ->and($response->json('files.0.path'))->toBe('uploads/article/featured.jpg');
});

it('rejects directories outside uploads', function () {
    actingAs(User::factory()->create());
    seedStorage();

    $this->getJson('/admin/media/api?dir=livewire-tmp')->assertStatus(422);
    $this->getJson('/admin/media/api?dir=uploads/../../app')->assertStatus(422);
});

it('uploads a file into the requested folder', function () {
    actingAs(User::factory()->create());
    seedStorage();

    $response = $this->postJson('/admin/media/api/upload', [
        'file' => UploadedFile::fake()->image('poster.png', 120, 80),
        'dir' => 'uploads/article/apps',
    ]);

    $response->assertCreated();
    expect($response->json('name'))->toBe('poster.png')
        ->and($response->json('path'))->toBe('uploads/article/apps/poster.png')
        ->and($response->json('rel'))->toBe('/storage/uploads/article/apps/poster.png')
        ->and(Storage::disk('public')->exists('uploads/article/apps/poster.png'))->toBeTrue();
});

it('uniquifies file names on collision', function () {
    actingAs(User::factory()->create());
    seedStorage();

    $this->postJson('/admin/media/api/upload', [
        'file' => UploadedFile::fake()->image('hero.jpg', 100, 60),
        'dir' => 'uploads/article',
    ])->assertCreated();

    $response = $this->postJson('/admin/media/api/upload', [
        'file' => UploadedFile::fake()->image('hero.jpg', 100, 60),
        'dir' => 'uploads/article',
    ])->assertCreated();

    expect($response->json('name'))->toBe('hero-1.jpg');
});

it('rejects uploads without a file', function () {
    actingAs(User::factory()->create());

    $this->postJson('/admin/media/api/upload', [])->assertUnprocessable();
});

it('creates folders', function () {
    actingAs(User::factory()->create());
    seedStorage();

    $response = $this->postJson('/admin/media/api/folder', [
        'dir' => 'uploads/article',
        'name' => 'banners',
    ]);

    $response->assertCreated();
    expect(Storage::disk('public')->directoryExists('uploads/article/banners'))->toBeTrue();

    $this->postJson('/admin/media/api/folder', [
        'dir' => 'uploads/article',
        'name' => 'banners',
    ])->assertUnprocessable();
});

it('deletes files', function () {
    actingAs(User::factory()->create());
    seedStorage();

    $this->postJson('/admin/media/api/delete', ['path' => 'uploads/article/featured.jpg'])->assertOk();
    expect(Storage::disk('public')->exists('uploads/article/featured.jpg'))->toBeFalse();
});

it('refuses to delete a folder that still has files', function () {
    actingAs(User::factory()->create());
    seedStorage();

    $this->postJson('/admin/media/api/delete', ['path' => 'uploads/article/apps'])
        ->assertUnprocessable();

    $this->postJson('/admin/media/api/delete', ['path' => 'uploads/article/empty-folder'])
        ->assertOk();

    expect(Storage::disk('public')->directoryExists('uploads/article/empty-folder'))->toBeFalse();
});
