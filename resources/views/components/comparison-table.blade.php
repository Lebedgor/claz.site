@props(['comparison'])

@php
    $locale = app()->getLocale();

    $criteria = $comparison->items
        ->flatMap(fn ($item) => $item->scores->map(fn ($score) => $score->criterion))
        ->filter()
        ->unique('id')
        ->sortBy('sort_order')
        ->values();

    $formatValue = function (mixed $raw, string $kind): string {
        $decoded = is_string($raw) ? json_decode($raw) : $raw;

        if ($kind === 'bool') {
            return in_array($decoded, [true, 1, '1', 'true'], true) ? '✓' : '—';
        }

        return match (true) {
            $decoded === false, $decoded === null => '—',
            default => strval(is_scalar($decoded) ? $decoded : $raw),
        };
    };
@endphp

<section class="my-10 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
    <div class="border-b border-zinc-100 p-5">
        <h2 class="text-xl font-semibold text-zinc-900">{{ $comparison->getTranslation('title', $locale) }}</h2>
        @if (filled($comparison->getTranslation('intro', $locale)))
            <p class="mt-2 text-sm text-zinc-600">{{ $comparison->getTranslation('intro', $locale) }}</p>
        @endif
    </div>
    <div class="overflow-x-auto p-5">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead>
                <tr class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500">
                    <th class="py-2 pr-4 font-semibold">{{ __('site.comparison.tool') }}</th>
                    <th class="py-2 pr-4 font-semibold">{{ __('site.comparison.score') }}</th>
                    @foreach ($criteria as $criterion)
                        <th class="py-2 pr-4 font-semibold">{{ $criterion->getTranslation('name', $locale) }}</th>
                    @endforeach
                    <th class="py-2 pr-4 font-semibold">{{ __('site.comparison.verdict') }}</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($comparison->items as $item)
                    <tr class="border-b border-zinc-100 align-top">
                        <td class="py-3 pr-4 font-medium text-zinc-900">
                            <a href="{{ $item->tool !== null ? route('tools.show', $item->tool) : '#' }}"
                               class="hover:text-indigo-700">
                                {{ $item->tool?->getTranslation('name', $locale) ?? '—' }}
                            </a>
                        </td>
                        <td class="py-3 pr-4">
                            @if ($item->score !== null)
                                <span class="rounded-lg bg-indigo-50 px-2 py-1 font-semibold text-indigo-700">
                                    {{ number_format((float) $item->score, 1) }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        @foreach ($criteria as $criterion)
                            @php $scoreRow = $item->scores->firstWhere('criteria_id', $criterion->getKey()); @endphp
                            <td class="py-3 pr-4 text-zinc-700">{{ $formatValue($scoreRow?->value, $criterion->kind->value) }}</td>
                        @endforeach
                        <td class="py-3 pr-4 text-zinc-600">{{ $item->getTranslation('verdict', $locale) }}</td>
                        <td class="py-3">
                            @php $link = $item->tool?->links->sortBy('sort_order')->first(); @endphp
                            @if ($link !== null)
                                <a href="/go/{{ $link->code }}"
                                   target="_blank"
                                   rel="sponsored nofollow noopener"
                                   class="inline-block whitespace-nowrap rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                                    {{ $link->getTranslation('anchor', $locale) ?: __('site.tool.visit') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if (filled($comparison->getTranslation('verdict', $locale)))
        <div class="border-t border-zinc-100 bg-zinc-50/60 p-5 text-sm text-zinc-700">
            <strong class="text-zinc-900">{{ __('site.comparison.verdict') }}:</strong>
            {{ $comparison->getTranslation('verdict', $locale) }}
        </div>
    @endif
</section>
