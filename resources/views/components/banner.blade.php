@props(['placement'])

@php
    $banner = \App\Models\Banner::query()->activeFor($placement)->first();
@endphp

@if ($banner !== null)
    <aside class="my-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        @if (filled($banner->html))
            {!! $banner->html !!}
        @elseif ($banner->image_url !== null)
            @if (filled($banner->url))
                <a href="{{ $banner->url }}" target="_blank" rel="sponsored nofollow noopener"
                   class="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full object-cover">
                </a>
            @else
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full object-cover">
            @endif
        @endif
    </aside>
@endif
