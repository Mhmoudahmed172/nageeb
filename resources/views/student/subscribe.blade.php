@extends('layouts.app')

@section('title', 'الاشتراك في '.$course->title.' — نجيب')

@section('content')
<x-dashboard-layout title="طلب اشتراك" role-label="الطالب" active-menu="courses">
    <div class="nageeb-card max-w-2xl mb-6">
        <h2 class="nageeb-title-section mb-2">{{ $course->title }}</h2>
        <p class="nageeb-text-muted">المعلّم: {{ $course->teacher->name }}</p>
    </div>

    @if ($course->accessPlans->isEmpty())
        <div class="nageeb-alert nageeb-alert--warning max-w-2xl">لا توجد خطط متاحة لهذه المادة حالياً.</div>
    @elseif (! $region)
        <div class="nageeb-alert nageeb-alert--warning max-w-2xl">تعذر تحديد منطقتك. أكمل ملفك الشخصي أولاً.</div>
    @else
        <form method="POST" action="{{ route('courses.subscribe.store', $course) }}" enctype="multipart/form-data" class="nageeb-card max-w-2xl grid gap-6">
            @csrf

            <fieldset class="grid gap-3">
                <legend class="nageeb-label mb-2">اختر خطة الوصول</legend>
                @foreach ($course->accessPlans as $plan)
                    @php $price = $plan->priceFor($region); @endphp
                    <label class="flex items-start gap-3 border border-border p-4 cursor-pointer">
                        <input
                            type="radio"
                            name="access_plan_id"
                            value="{{ $plan->id }}"
                            class="mt-1"
                            required
                            @checked(old('access_plan_id') == $plan->id)
                        >
                        <span>
                            <span class="block font-medium">{{ $plan->title }}</span>
                            <span class="nageeb-text-muted text-sm">
                                {{ $plan->semesters->pluck('title')->join('، ') }}
                                @if ($price)
                                    — {{ number_format((float) $price->effectivePrice(), 2) }} ₪
                                @endif
                            </span>
                        </span>
                    </label>
                @endforeach
                @error('access_plan_id')
                    <p class="nageeb-field-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="nageeb-field">
                <label for="receipt" class="nageeb-label">صورة إيصال الدفع <span class="text-alert">*</span></label>
                <input type="file" id="receipt" name="receipt" required accept=".jpg,.jpeg,.png,.pdf" class="nageeb-input">
                <p class="nageeb-text-dim text-sm mt-1">الصيغ المسموحة: jpg، png، pdf</p>
                @error('receipt')
                    <p class="nageeb-field-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="nageeb-btn nageeb-btn--primary justify-self-start">إرسال الطلب</button>
        </form>
    @endif
</x-dashboard-layout>
@endsection
