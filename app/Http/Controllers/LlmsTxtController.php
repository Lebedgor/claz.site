<?php

namespace App\Http\Controllers;

use App\Enums\ToolStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tool;
use App\Services\ArticleRenderer;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LlmsTxtController extends Controller
{
    public function index(): Response
    {
        $content = Cache::remember('llms-txt', 3600, fn (): string => $this->buildIndex());

        return response($content)->header('Content-Type', 'text/markdown; charset=UTF-8');
    }

    public function full(): Response
    {
        $content = Cache::remember('llms-full-txt', 21600, fn (): string => $this->buildFull());

        return response($content)->header('Content-Type', 'text/markdown; charset=UTF-8');
    }

    private function buildIndex(): string
    {
        $locale = app()->getLocale();
        $lines = [
            '# '.config('app.name'),
            '',
            '> '.__('site.tagline'),
            '',
            __('site.llms.about'),
            '',
        ];

        $articles = Article::query()->published()->with('category')->orderByDesc('published_at')->get();

        if ($articles->isNotEmpty()) {
            $lines[] = '## Articles';
            $lines[] = '';

            foreach ($articles as $article) {
                $lines[] = '- ['.$article->getTranslation('title', $locale).']('.route('articles.show', $article).'): '
                    .$this->summary($article->getTranslation('excerpt', $locale) ?: $article->getTranslation('body_html', $locale));
            }
            $lines[] = '';
        }

        $categories = Category::query()->orderBy('id')->get();

        if ($categories->isNotEmpty()) {
            $lines[] = '## Categories';
            $lines[] = '';

            foreach ($categories as $category) {
                $lines[] = '- ['.$category->getTranslation('name', $locale).']('.route('category.show', $category).')';
            }
            $lines[] = '';
        }

        $tools = Tool::query()->where('status', ToolStatus::Published->value)->orderByRaw('rating_avg desc nulls last')->get();

        if ($tools->isNotEmpty()) {
            $lines[] = '## Tools';
            $lines[] = '';

            foreach ($tools as $tool) {
                $lines[] = '- ['.$tool->getTranslation('name', $locale).']('.route('tools.show', $tool).'): '
                    .$this->summary($tool->getTranslation('description', $locale));
            }
            $lines[] = '';
        }

        return implode(PHP_EOL, $lines);
    }

    private function buildFull(): string
    {
        $locale = app()->getLocale();
        $renderer = app(ArticleRenderer::class);
        $lines = [
            '# '.config('app.name').' — full content',
            '',
            '> '.__('site.tagline'),
            '',
        ];

        $articles = Article::query()->published()->with('category', 'tags')->orderByDesc('published_at')->get();

        foreach ($articles as $article) {
            $body = $renderer->render($article);

            $lines[] = '---';
            $lines[] = '';
            $lines[] = '# '.$article->getTranslation('title', $locale);
            $lines[] = '';
            $lines[] = __('site.llms.published').': '.$article->published_at?->toDateString()
                .' · '.__('site.llms.updated').': '.$article->updated_at?->toDateString()
                .' · '.__('site.llms.url').': '.route('articles.show', $article);
            $lines[] = '';
            $lines[] = $this->textFromHtml($body);
            $lines[] = '';
        }

        return implode(PHP_EOL, $lines);
    }

    private function summary(string $html): string
    {
        return (string) Str::limit($this->textFromHtml($html), 220);
    }

    private function textFromHtml(string $html): string
    {
        $withoutTables = (string) preg_replace('/<table.*?<\/table>/is', ' ', $html);
        $text = html_entity_decode(strip_tags($withoutTables), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/[ \t]+/u', ' ', $text));
    }
}
