<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GoController;
use App\Http\Controllers\HomeController;
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
