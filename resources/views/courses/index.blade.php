@extends('layouts.app')

@section('title', 'المواد المتاحة — نجيب')

@section('content')
<x-public-layout title="المواد المتاحة" current="courses">
    @if ($courses->isEmpty())
        <x-empty-state title="لا توجد مواد منشورة حالياً." action-href="{{ url('/') }}" action-label="العودة للرئيسية" />
    @else
        <x-reveal stagger class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                <div class="nageeb-reveal-item">
                    <x-course-card
                        variant="marketplace"
                        :title="$course->title"
                        :teacher="$course->teacher->name"
                        :image="\App\Support\NageebVisual::courseCover($course, $loop->index)"
                        :avatar="\App\Support\NageebVisual::teacherPhoto($course->teacher)"
                        :subject="\App\Support\NageebVisual::subjectLabel($course->title)"
                        :grade="$course->grade_level?->label()"
                        :region="$course->is_free ? null : 'غزة والضفة'"
                        :price="$course->is_free ? 'مجاني' : ($course->reference_price ? 'من '.number_format((float) $course->reference_price).' ₪' : null)"
                        :href="$course->is_free ? route('student.my-courses.show', $course) : route('courses.subscribe', $course)"
                        :cta="$course->is_free ? 'دخول مجاني' : 'الاشتراك'"
                    />
                </div>
            @endforeach
        </x-reveal>
    @endif
</x-public-layout>
@endsection
