@props(['items'])

@php
    $crumbs = collect($items)->values()->all();
    $relativeOf = function (string $link): string {
        $base = rtrim(url('/'), '/');

        if (! str_starts_with($link, $base)) {
            return $link;
        }

        return mb_strlen($link) === mb_strlen($base) ? '/' : mb_substr($link, mb_strlen($base));
    };
    $breadcrumbLd = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($crumbs)->map(fn (array $crumb, int $i): array => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => strval($crumb['label']),
            'item' => filled($crumb['url'] ?? null) ? url($crumb['url']) : url()->current(),
        ])->all(),
    ];
@endphp

<nav aria-label="Breadcrumb" class="text-sm text-zinc-500 dark:text-zinc-400">
    <ol class="flex flex-wrap items-center gap-y-1">
        @foreach ($crumbs as $i => $crumb)
            <li class="flex items-center">
                @if ($i < count($crumbs) - 1 && filled($crumb['url'] ?? null))
                    <a href="{{ $relativeOf(strval($crumb['url'])) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">{{ $crumb['label'] }}</a>
                    <span class="mx-1" aria-hidden="true">/</span>
                @else
                    <span class="text-zinc-900 dark:text-zinc-100" aria-current="page">{{ $crumb['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
<script type="application/ld+json">@json($breadcrumbLd)</script>
