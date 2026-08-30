@extends('layouts.app')

@section('title', 'لوحة الطالب — نجيب')

@section('content')
<x-dashboard-layout title="لوحة الطالب" role-label="الطالب" active-menu="dashboard">
    <x-reveal class="mb-8">
        <p class="nageeb-caption mb-2">{{ now()->locale('ar')->translatedFormat('l، j F') }}</p>
        <h2 class="nageeb-heading-1">مرحبًا، {{ $user->name }}</h2>
        <p class="nageeb-text-muted mt-2">أكمل من حيث توقفت، وتابع اختباراتك وموادك.</p>
    </x-reveal>

    <x-reveal class="mb-10">
        @if ($lastLesson)
            <x-continue-learning
                kicker="{{ $lastLesson->unit?->course?->title ?? 'أكمل التعلّم' }}"
                :title="$lastLesson->title"
                subtitle="{{ $enrolledCount }} مواد مفعّلة في حسابك"
                :image="\App\Support\NageebVisual::courseCover($lastLesson->unit?->course)"
                :href="route('student.my-courses.show', ['course' => $lastLesson->unit->semester->course, 'lesson' => $lastLesson->id])"
                cta="متابعة التعلّم"
            />
        @else
            <x-continue-learning
                kicker="ابدأ من هنا"
                title="لم تفتح درساً بعد."
                subtitle="ادخل إلى مادة ملتحق بها ليُحفظ آخر درس هنا."
                image="{{ asset('images/nageeb/courses/dashboard-cover.png') }}"
                :href="route('student.my-courses.index')"
                cta="فتح موادي"
            />
        @endif
    </x-reveal>

    <x-reveal stagger class="grid gap-6 lg:grid-cols-3 mb-10">
        <a href="{{ route('student.exams.index') }}" class="nageeb-dash-panel nageeb-reveal-item text-text hover:text-text">
            <x-nageeb-img path="illustrations/exams.png" alt="" class="size-12 object-contain mb-3" />
            <p class="nageeb-type-caption">الاختبارات القادمة</p>
            <h3 class="nageeb-type-h3 mt-1">اختبارات موادك</h3>
            <p class="nageeb-type-body-sm nageeb-text-muted mt-2">تظهر عند فتح المعلّم لها.</p>
        </a>
        <a href="{{ route('courses.index') }}" class="nageeb-dash-panel nageeb-reveal-item text-text hover:text-text">
            <x-nageeb-img path="illustrations/learning.png" alt="" class="size-12 object-contain mb-3" />
            <p class="nageeb-type-caption">مواد موصى بها</p>
            <h3 class="nageeb-type-h3 mt-1">استكشف الكتالوج</h3>
            <p class="nageeb-type-body-sm nageeb-text-muted mt-2">مواد حيّة من معلّمين موثّقين.</p>
        </a>
        <a href="{{ route('student.my-courses.index') }}" class="nageeb-dash-panel nageeb-reveal-item text-text hover:text-text">
            <x-nageeb-img path="illustrations/progress.png" alt="" class="size-12 object-contain mb-3" />
            <p class="nageeb-type-caption">إنجازك</p>
            <h3 class="nageeb-type-h3 mt-1">{{ $enrolledCount }} مواد مفعّلة</h3>
            <p class="nageeb-type-body-sm nageeb-text-muted mt-2">تابع إكمال الدروس من موادي.</p>
        </a>
    </x-reveal>

    <x-reveal>
        <section class="nageeb-account-strip">
            <h3 class="nageeb-type-h4 mb-4">حسابك</h3>
            <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                <div>
                    <dt class="nageeb-text-dim mb-1">البريد</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="nageeb-text-dim mb-1">الجوال</dt>
                    <dd>{{ $user->phone }}</dd>
                </div>
                <div>
                    <dt class="nageeb-text-dim mb-1">المنطقة</dt>
                    <dd>{{ $profile?->region?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="nageeb-text-dim mb-1">الدور</dt>
                    <dd><span class="nageeb-badge nageeb-badge--primary">{{ $user->role->label() }}</span></dd>
                </div>
            </dl>
        </section>
    </x-reveal>
</x-dashboard-layout>
@endsection
