@props(['items' => []])

<nav aria-label="مسار التنقل" {{ $attributes }}>
    <ol class="nageeb-breadcrumbs">
        @foreach ($items as $item)
            <li>
                @if (! $loop->last && isset($item['href']))
                    <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                @else
                    <span @if($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
