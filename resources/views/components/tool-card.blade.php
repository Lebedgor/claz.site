@props(['tool'])

@php
    $locale = app()->getLocale();
    $name = $tool->getTranslation('name', $locale);
    $typeGradients = [
        \App\Enums\ToolType::Plugin->value => 'from-violet-500 to-purple-600',
        \App\Enums\ToolType::Service->value => 'from-sky-500 to-blue-600',
        \App\Enums\ToolType::Ai->value => 'from-fuchsia-500 to-pink-600',
    ];
    $typeTints = [
        \App\Enums\ToolType::Plugin->value => 'bg-violet-50 text-violet-700 ring-violet-100',
        \App\Enums\ToolType::Service->value => 'bg-sky-50 text-sky-700 ring-sky-100',
        \App\Enums\ToolType::Ai->value => 'bg-fuchsia-50 text-fuchsia-700 ring-fuchsia-100',
    ];
@endphp

<a href="{{ route('tools.show', $tool) }}"
   class="group flex flex-col gap-3 rounded-2xl bg-white p-5 ring-1 ring-zinc-200/80 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/10 hover:ring-indigo-200">
    <div class="flex items-center gap-3">
        @if ($tool->logo_url !== null)
            <img src="{{ $tool->logo_url }}" alt="{{ $name }}" loading="lazy" decoding="async"
                 class="h-11 w-11 shrink-0 rounded-xl object-cover ring-1 ring-zinc-200">
        @else
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-linear-to-br {{ $typeGradients[$tool->type->value] }} text-sm font-bold text-white shadow-md">
                {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
            </div>
        @endif
        <div class="min-w-0">
            <h3 class="truncate font-semibold text-zinc-900 transition group-hover:text-indigo-600">{{ $name }}</h3>
            @if (filled($tool->vendor))
                <p class="truncate text-xs text-zinc-500">{{ $tool->vendor }}</p>
            @endif
        </div>
        @if ($tool->rating_avg !== null)
            <span class="ml-auto inline-flex shrink-0 items-center gap-1 rounded-lg bg-linear-to-br from-amber-50 to-amber-100 px-2 py-1 text-sm font-bold text-amber-700 ring-1 ring-amber-200">
                <svg viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-amber-500" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.364 1.118l1.287 3.957c.3.922-.755 1.688-1.539 1.118l-3.366-2.445a1 1 0 0 0-1.176 0l-3.367 2.445c-.783.57-1.838-.196-1.538-1.118l1.286-3.957a1 1 0 0 0-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.285-3.958Z"/>
                </svg>
                {{ number_format((float) $tool->rating_avg, 1) }}
            </span>
        @endif
    </div>
    <p class="line-clamp-2 text-sm text-zinc-600">
        {{ \Illuminate\Support\Str::limit(strval($tool->getTranslation('description', $locale)), 120) }}
    </p>
    <div class="mt-auto flex items-center justify-between">
        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide ring-1 {{ $typeTints[$tool->type->value] }}">
            {{ __('site.tools_index.type.'.$tool->type->value) }}
        </span>
        <span aria-hidden="true" class="-translate-x-1 text-indigo-600 opacity-0 transition duration-300 group-hover:translate-x-0 group-hover:opacity-100">
            <x-heroicon-o-arrow-right class="h-4 w-4" />
        </span>
    </div>
</a>
