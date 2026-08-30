@props([
    'title',
    'course',
    'semester' => null,
    'unit' => null,
    'thumbnail' => 'lessons/lesson-thumbnail.png',
    'progress' => null,
    'state' => 'ready',
    'nextLesson' => null,
])

<section {{ $attributes->class('nageeb-lesson-player') }}>
    <div class="nageeb-lesson-player__stage">
        <x-nageeb-img :path="$thumbnail" alt="" />

        @if ($state === 'protected')
            <div class="nageeb-lesson-player__locked">
                <div>
                    <p class="nageeb-type-h4 text-white">محتوى محمي للمشتركين</p>
                    <p class="nageeb-type-body-sm mt-2 text-white/80 max-w-sm mx-auto">اشترك في خطة المعلّم لهذه المادة لمشاهدة الدرس والمرفقات.</p>
                    <x-button variant="primary" size="sm" class="mt-4">عرض خطط الوصول</x-button>
                </div>
            </div>
        @elseif ($state === 'completed')
            <div class="nageeb-play">
                <x-badge variant="success">مكتمل</x-badge>
            </div>
        @else
            <div class="nageeb-play">
                <button type="button" class="nageeb-play__btn" aria-label="تشغيل الدرس">
                    <svg class="size-6 ms-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v13.72L19.12 12 8 5.14Z"/></svg>
                </button>
            </div>
        @endif
    </div>

    <div class="nageeb-lesson-player__body">
        <p class="nageeb-lesson-player__path">
            <span>{{ $course }}</span>
            @if ($semester)<span>‹ {{ $semester }}</span>@endif
            @if ($unit)<span>‹ {{ $unit }}</span>@endif
        </p>
        <h3 class="nageeb-type-h3">{{ $title }}</h3>
        @if ($progress !== null)
            <x-progress :value="$progress" label="تقدم الدرس" class="mt-4" />
        @endif
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p class="nageeb-type-caption">مرفق PDF · ورقة عمل الدرس</p>
            @if ($nextLesson)
                <x-button variant="ghost" size="sm">التالي: {{ $nextLesson }}</x-button>
            @endif
        </div>
    </div>
</section>
