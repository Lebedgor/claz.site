<?php

namespace App\Livewire;

use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Support\HtmlSanitizer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ArticleComments extends Component
{
    public Article $article;

    public bool $submitted = false;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|min:3|max:5000')]
    public string $body = '';

    public string $website = '';

    public function mount(Article $article): void
    {
        $this->article = $article;
    }

    public function submit(): void
    {
        $this->submitted = false;
        $this->validate();

        $ipHash = hash('sha256', strval(request()->ip()).'|'.config('app.key'));
        $attemptsKey = 'comment-attempts:'.$ipHash;
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= 3) {
            $this->addError('body', __('site.comments.too_many'));

            return;
        }

        if (filled($this->website)) {
            $this->reset(['name', 'email', 'body', 'website']);

            return;
        }

        Comment::create([
            'article_id' => $this->article->getKey(),
            'name' => $this->name,
            'email' => $this->email,
            'body' => HtmlSanitizer::clean($this->body),
            'status' => CommentStatus::Pending,
            'ip_hash' => $ipHash,
        ]);

        Cache::put($attemptsKey, $attempts + 1, 3600);

        $this->reset(['name', 'email', 'body', 'website']);
        $this->submitted = true;
    }

    public function render(): View
    {
        $comments = Comment::query()
            ->approved()
            ->where('article_id', $this->article->getKey())
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.article-comments', ['comments' => $comments]);
    }
}
