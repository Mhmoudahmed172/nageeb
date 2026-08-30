@props([
    'title' => 'اختبار الوحدة الأولى',
    'course' => 'اللغة العربية',
    'current' => 3,
    'total' => 12,
    'timer' => '24:18',
    'progress' => 25,
    'question' => 'ما الإعراب الصحيح لكلمة «المعلم» في الجملة: كافأ المديرُ المعلمَ المجتهد؟',
    'options' => [
        'فاعل مرفوع',
        'مفعول به منصوب',
        'مضاف إليه مجرور',
        'خبر منصوب',
    ],
    'selected' => 1,
    'type' => 'single',
])

<section {{ $attributes->class('nageeb-exam') }}>
    <div class="nageeb-exam__bar">
        <div>
            <p class="nageeb-type-caption">{{ $course }}</p>
            <h3 class="nageeb-type-h4">{{ $title }}</h3>
        </div>
        <div class="flex items-center gap-3">
            <span class="nageeb-exam__timer" aria-label="الوقت المتبقي">{{ $timer }}</span>
            <span class="nageeb-type-caption tabular-nums">{{ $current }} / {{ $total }}</span>
        </div>
    </div>

    <div class="nageeb-exam__body space-y-5">
        <x-progress :value="$progress" label="تقدم الاختبار" />

        <div>
            <p class="nageeb-type-label mb-2">{{ $type === 'multi' ? 'اختيار من متعدد — أكثر من إجابة' : 'اختيار من متعدد' }}</p>
            <p class="nageeb-type-h4 leading-relaxed">{{ $question }}</p>
        </div>

        <div class="grid gap-2">
            @foreach ($options as $index => $option)
                <label class="nageeb-choice {{ $selected === $index ? 'nageeb-choice--selected' : '' }}">
                    <input
                        type="{{ $type === 'multi' ? 'checkbox' : 'radio' }}"
                        name="ds-exam-option"
                        @checked($selected === $index)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>

        <div class="nageeb-exam-nav" aria-label="التنقل بين الأسئلة">
            @for ($i = 1; $i <= $total; $i++)
                <button
                    type="button"
                    @class(['is-done' => $i < $current, 'font-mono'])
                    @if ($i === $current) aria-current="true" @endif
                >{{ $i }}</button>
            @endfor
        </div>

        <div class="flex flex-wrap justify-between gap-2 pt-1">
            <x-button variant="secondary" size="sm">السابق</x-button>
            <x-button variant="primary" size="sm">السؤال التالي</x-button>
        </div>
    </div>
</section>
