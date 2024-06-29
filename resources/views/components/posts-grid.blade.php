@props(['posts'])

<x-post-featured-card :post="$posts[0]" />

@if ($posts->count() > 1)
    <div class="lg:grid lg:grid-cols-6" x-for="item in items">
        @foreach ($posts->skip(1) as $post)
            <x-post-card :post="$post" class="{{ $loop->iteration < 3 ? 'col-span-3' : 'col-span-2' }}" />
        @endforeach
    </div>
    <span>Loading...</span>
@endif
