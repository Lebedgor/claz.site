<section class="mt-12">
    <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('site.comments.title') }}</h2>

    <div class="mt-4 space-y-4">
        @forelse ($comments as $comment)
            <article class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-2 text-sm">
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $comment->name }}</span>
                    <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $comment->created_at?->format('M j, Y') }}</span>
                </div>
                <div class="mt-2 text-sm leading-relaxed text-zinc-700 [&_a]:text-indigo-600 [&_p]:mt-2 dark:text-zinc-300 dark:[&_a]:text-indigo-400">
                    {!! $comment->body !!}
                </div>
            </article>
        @empty
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('site.comments.empty') }}</p>
        @endforelse
    </div>

    <form wire:submit="submit" class="mt-8 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('site.comments.leave') }}</h3>

        @if ($submitted)
            <p class="mt-3 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-950 dark:text-green-400">
                {{ __('site.comments.pending_notice') }}
            </p>
        @endif

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400" for="comment-name">{{ __('site.comments.name') }}</label>
                <input id="comment-name" type="text" wire:model="name"
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-indigo-400">
                @error('name') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400" for="comment-email">{{ __('site.comments.email') }}</label>
                <input id="comment-email" type="email" wire:model="email"
                       class="mt-1 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-indigo-400">
                @error('email') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-4">
            <label class="text-xs font-medium text-zinc-500 dark:text-zinc-400" for="comment-body">{{ __('site.comments.body') }}</label>
            <textarea id="comment-body" wire:model="body" rows="4"
                      class="mt-1 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:border-indigo-400"></textarea>
            @error('body') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="hidden" aria-hidden="true">
            <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <button type="submit"
                class="mt-4 inline-block rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
            {{ __('site.comments.submit') }}
        </button>
    </form>
</section>
