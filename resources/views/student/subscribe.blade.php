@extends('layouts.app')

@section('title', 'الاشتراك في '.$course->title.' — نجيب')

@section('content')
<x-dashboard-layout title="طلب اشتراك" role-label="الطالب" active-menu="courses">
    <div class="nageeb-card max-w-2xl mb-6">
        <h2 class="nageeb-title-section mb-2">{{ $course->title }}</h2>
        <p class="nageeb-text-muted">المعلّم: {{ $course->teacher->name }}</p>
    </div>

    @if ($course->packages->isEmpty())
        <div class="nageeb-alert nageeb-alert--warning max-w-2xl">لا توجد باقات متاحة لهذه المادة حالياً.</div>
    @elseif (! $region)
        <div class="nageeb-alert nageeb-alert--warning max-w-2xl">تعذر تحديد منطقتك. أكمل ملفك الشخصي أولاً.</div>
    @else
        <form method="POST" action="{{ route('courses.subscribe.store', $course) }}" enctype="multipart/form-data" class="nageeb-card max-w-2xl grid gap-6">
            @csrf

            <fieldset class="grid gap-3">
                <legend class="nageeb-label mb-2">اختر الباقة</legend>
                @foreach ($course->packages as $package)
                    <label class="flex items-start gap-3 border border-border p-4 cursor-pointer">
                        <input
                            type="radio"
                            name="package_id"
                            value="{{ $package->id }}"
                            class="mt-1"
                            required
                            @checked(old('package_id') == $package->id)
                        >
                        <span>
                            <span class="block font-medium">{{ $package->name }}</span>
                            <span class="nageeb-text-muted text-sm">{{ $package->duration_label }} — {{ number_format($package->priceFor($region), 2) }} ₪</span>
                        </span>
                    </label>
                @endforeach
                @error('package_id')
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
