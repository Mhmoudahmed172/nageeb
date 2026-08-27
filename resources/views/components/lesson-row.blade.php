@props(['title', 'index' => null, 'duration' => null, 'status' => null, 'href' => null])

@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class('nageeb-lesson-row text-text hover:text-text') }}>
    <span class="nageeb-lesson-row__index">{{ $index ?? '•' }}</span>
    <span class="min-w-0">
        <span class="font-semibold text-sm block truncate">{{ $title }}</span>
        @if ($duration)<span class="nageeb-caption">{{ $duration }}</span>@endif
    </span>
    @if ($status)<x-badge variant="{{ $status === 'مكتمل' ? 'success' : 'info' }}">{{ $status }}</x-badge>@endif
</{{ $tag }}>
