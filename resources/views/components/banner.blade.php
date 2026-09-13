@props(['placement'])

@php
    $banner = \App\Models\Banner::query()->activeFor($placement)->first();
@endphp

@if ($banner !== null)
    <aside class="my-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        @if (filled($banner->html))
            {!! $banner->html !!}
        @elseif ($banner->image_url !== null)
            @if (filled($banner->url))
                <a href="{{ $banner->url }}" target="_blank" rel="sponsored nofollow noopener">
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full object-cover">
                </a>
            @else
                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full object-cover">
            @endif
        @endif
    </aside>
@endif
