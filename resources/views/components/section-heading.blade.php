@props(['title', 'linkHref' => null, 'linkText' => null])

<div class="flex items-end justify-between gap-4">
    <h2 class="flex items-center gap-3 text-2xl font-bold tracking-tight text-zinc-900 sm:text-3xl">
        <span aria-hidden="true" class="h-8 w-1.5 shrink-0 rounded-full bg-linear-to-b from-indigo-500 to-fuchsia-500"></span>
        {{ $title }}
    </h2>
    @if (filled($linkHref))
        <a href="{{ $linkHref }}"
           class="hidden items-center gap-1 rounded-full px-4 py-2 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 sm:inline-flex">
            {{ $linkText }}
            <x-heroicon-o-arrow-right class="h-4 w-4" />
        </a>
    @endif
</div>
