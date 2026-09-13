<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticlePreviewController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LlmsTxtController;
use App\Http\Controllers\MediaApiController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\VsPageController;
use App\Livewire\ToolsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/category/{category}', CategoryController::class)->name('category.show');
Route::get('/articles/{article}', ArticleController::class)->name('articles.show');
Route::get('/tools', ToolsIndex::class)->name('tools.index');
Route::get('/tools/{tool}', ToolController::class)->name('tools.show');
Route::get('/vs/{slugs}', VsPageController::class)->where('slugs', '[a-z0-9\-]+')->name('vs.show');
Route::get('/go/{link:code}', GoController::class)->where('link', '[A-Za-z0-9\-_]+')->name('go.show');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/rss.xml', RssController::class)->name('rss');
Route::get('/llms.txt', [LlmsTxtController::class, 'index'])->name('llms');
Route::get('/llms-full.txt', [LlmsTxtController::class, 'full'])->name('llms-full');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/media/api', [MediaApiController::class, 'index'])->name('media.api.index');
    Route::post('/media/api/upload', [MediaApiController::class, 'upload'])->name('media.api.upload');
    Route::post('/media/api/folder', [MediaApiController::class, 'createFolder'])->name('media.api.folder');
    Route::post('/media/api/delete', [MediaApiController::class, 'delete'])->name('media.api.delete');
    Route::get('/preview/articles/{article}', ArticlePreviewController::class)->name('articles.preview');
});
