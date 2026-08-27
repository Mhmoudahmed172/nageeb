@extends('layouts.app')

@section('title', $course->title.' — نظرة عامة')

@section('content')
<x-course-workspace :course="$course" active="overview">
    <dl class="course-overview-stats">
        <div>
            <dt class="text-sm nageeb-text-muted">الوحدات</dt>
            <dd class="price text-2xl font-bold">{{ $course->units_count }}</dd>
        </div>
        <div>
            <dt class="text-sm nageeb-text-muted">الدروس</dt>
            <dd class="price text-2xl font-bold">{{ $course->lessons_count }}</dd>
        </div>
        <div>
            <dt class="text-sm nageeb-text-muted">خطط الوصول</dt>
            <dd class="price text-2xl font-bold">{{ $course->access_plans_count }}</dd>
        </div>
        <div>
            <dt class="text-sm nageeb-text-muted">الطلاب</dt>
            <dd class="price text-2xl font-bold">{{ $course->enrollments_count }}</dd>
        </div>
    </dl>
    <p class="nageeb-text-muted mt-8">انتقل إلى تبويب الوحدات والدروس لبناء المنهج.</p>
</x-course-workspace>
@endsection
