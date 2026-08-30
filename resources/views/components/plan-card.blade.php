@props([
    'title',
    'course',
    'grade' => null,
    'gazaPrice',
    'westBankPrice',
    'duration' => null,
    'lessons' => null,
    'includes' => [],
    'cta' => 'اختيار هذه الخطة',
    'featured' => false,
    'badge' => null,
])

<article {{ $attributes->class(['nageeb-plan-card', 'nageeb-plan-card--featured' => $featured]) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="nageeb-type-label text-primary">{{ $title }}</p>
            <h3 class="nageeb-type-h4 mt-1">{{ $course }}</h3>
            @if ($grade)
                <p class="nageeb-type-body-sm nageeb-text-muted mt-0.5">{{ $grade }}</p>
            @endif
        </div>
        @if ($badge)
            <x-badge :variant="$featured ? 'primary' : 'secondary'">{{ $badge }}</x-badge>
        @endif
    </div>

    <div class="nageeb-plan-card__prices">
        <div class="nageeb-plan-price">
            <span class="nageeb-type-caption">غزة</span>
            <strong class="tabular-nums">{{ $gazaPrice }} ₪</strong>
        </div>
        <div class="nageeb-plan-price">
            <span class="nageeb-type-caption">الضفة الغربية</span>
            <strong class="tabular-nums">{{ $westBankPrice }} ₪</strong>
        </div>
    </div>

    @if ($duration || $lessons)
        <p class="nageeb-type-caption mb-3">
            @if ($duration){{ $duration }}@endif
            @if ($duration && $lessons) · @endif
            @if ($lessons){{ $lessons }}@endif
        </p>
    @endif

    @if (count($includes))
        <ul class="nageeb-plan-card__list">
            @foreach ($includes as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @endif

    <x-button :variant="$featured ? 'primary' : 'outline'" class="w-full mt-auto">{{ $cta }}</x-button>
</article>
