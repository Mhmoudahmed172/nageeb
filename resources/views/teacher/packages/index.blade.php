@extends('layouts.app')

@section('title', 'خطط الوصول — نجيب')

@section('content')
<x-course-workspace :course="$course" active="packages">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-7">
        <div>
            <h2 class="nageeb-heading-2">خطط الوصول</h2>
            <p class="nageeb-text-muted text-sm mt-1">حدّد كيف يحصل طلابك على فصول هذه المادة وبأي سعر لكل منطقة.</p>
        </div>
        <x-button x-on:click="$dispatch('open-modal', 'create-access-plan')">+ إنشاء خطة وصول</x-button>
    </div>

    @if ($plans->isEmpty())
        <x-card class="py-12 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-xl bg-primary-muted text-primary mb-4">
                <x-nav-icon name="subscription" class="size-7" />
            </span>
            <h3 class="nageeb-heading-2">أنشئ أول خطة وصول لهذه المادة.</h3>
            <p class="nageeb-text-muted text-sm mt-2 mb-6">اختر الفصول المشمولة وحدد السعر المناسب لكل منطقة طالب.</p>
            <x-button x-on:click="$dispatch('open-modal', 'create-access-plan')">+ إنشاء خطة وصول</x-button>
        </x-card>
    @else
        <div class="access-plan-list">
            <div class="access-plan-list__head">
                <span>اسم الخطة</span>
                <span>الفصول المشمولة</span>
                <span>السعر حسب المنطقة</span>
                <span>مدة الوصول</span>
                <span>الحالة</span>
                <span>المبيعات</span>
                <span class="sr-only">الإجراءات</span>
            </div>

            @foreach ($plans as $plan)
                <article class="access-plan-row" x-data="{ editing: false }">
                    <div class="min-w-0">
                        <h3 class="font-bold truncate">{{ $plan->title }}</h3>
                        @if ($plan->description)<p class="nageeb-caption mt-1 line-clamp-2">{{ $plan->description }}</p>@endif
                    </div>
                    <div>
                        <span class="access-plan-mobile-label">الفصول التي يحصل الطالب عليها</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($plan->semesters as $semester)
                                <x-badge variant="info">{{ $semester->title }}</x-badge>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="access-plan-mobile-label">السعر حسب منطقة الطالب</span>
                        <div class="grid gap-1.5">
                            @foreach ($plan->prices as $price)
                                <span class="flex items-center justify-between gap-3 text-sm">
                                    <span class="nageeb-text-muted">{{ $price->region->name }}</span>
                                    <strong class="font-mono">{{ number_format($price->effectivePrice(), 0) }} ₪</strong>
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="access-plan-mobile-label">مدة الوصول</span>
                        <span class="text-sm">{{ $plan->access_duration_days ? $plan->access_duration_days.' يوم' : 'غير محدودة' }}</span>
                    </div>
                    <div>
                        <span class="access-plan-mobile-label">الحالة</span>
                        <x-badge variant="{{ $plan->status === \App\Enums\ContentStatus::Live ? 'success' : 'warning' }}">{{ $plan->status->label() }}</x-badge>
                    </div>
                    <div>
                        <span class="access-plan-mobile-label">المبيعات</span>
                        <strong class="font-mono">{{ $plan->enrollments_count }}</strong>
                    </div>
                    <div class="flex items-center justify-end gap-1">
                        <x-button variant="ghost" size="sm" x-on:click="editing = !editing">تعديل</x-button>
                        <form method="POST" action="{{ route('teacher.courses.packages.destroy', [$course, $plan]) }}" onsubmit="return confirm('حذف خطة الوصول؟')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="ghost" size="sm" class="text-danger" :disabled="$plan->enrollments_count > 0">حذف</x-button>
                        </form>
                    </div>

                    <div x-show="editing" x-transition x-cloak class="access-plan-row__edit">
                        <form method="POST" action="{{ route('teacher.courses.packages.update', [$course, $plan]) }}" class="grid gap-6">
                            @csrf
                            @method('PUT')
                            @include('teacher.packages._form', ['editingPlan' => $plan, 'formKey' => 'edit-'.$plan->id])
                            <div class="flex justify-end gap-2">
                                <x-button type="button" variant="ghost" x-on:click="editing = false">إلغاء</x-button>
                                <x-button type="submit">حفظ التعديلات</x-button>
                            </div>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <x-modal name="create-access-plan" title="إنشاء خطة وصول" description="هذه الخطة خاصة بمادة «{{ $course->title }}» وتحت تحكمك بالكامل.">
        <form method="POST" action="{{ route('teacher.courses.packages.store', $course) }}" class="grid gap-6">
            @csrf
            @include('teacher.packages._form', ['editingPlan' => null, 'formKey' => 'create'])
            <div class="flex justify-end gap-2">
                <x-button type="button" variant="ghost" x-on:click="$dispatch('close-modal', 'create-access-plan')">إلغاء</x-button>
                <x-button type="submit">إنشاء الخطة</x-button>
            </div>
        </form>
    </x-modal>
</x-course-workspace>
@endsection
