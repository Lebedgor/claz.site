@props(['tool'])

@php
    $locale = app()->getLocale();
    $name = $tool->getTranslation('name', $locale);
@endphp

<a href="{{ route('tools.show', $tool) }}"
   class="group flex flex-col gap-3 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:shadow-md">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white">
            {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <h3 class="truncate font-semibold text-zinc-900 group-hover:text-indigo-700">{{ $name }}</h3>
            @if (filled($tool->vendor))
                <p class="truncate text-xs text-zinc-500">{{ $tool->vendor }}</p>
            @endif
        </div>
        @if ($tool->rating_avg !== null)
            <span class="ml-auto rounded-lg bg-amber-50 px-2 py-1 text-sm font-semibold text-amber-700">
                {{ number_format((float) $tool->rating_avg, 1) }}
            </span>
        @endif
    </div>
    <p class="line-clamp-2 text-sm text-zinc-600">
        {{ \Illuminate\Support\Str::limit(strval($tool->getTranslation('description', $locale)), 120) }}
    </p>
    <span class="text-xs font-medium uppercase tracking-wide text-indigo-600">
        {{ __('site.tools_index.type.'.$tool->type->value) }}
    </span>
</a>
