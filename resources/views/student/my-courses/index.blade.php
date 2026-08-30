@extends('layouts.app')

@section('title', 'موادي — نجيب')

@section('content')
<x-dashboard-layout title="موادي" role-label="الطالب" active-menu="courses">
    @if ($courses->isEmpty())
        <x-card class="max-w-xl">
            <x-empty-state title="لا يوجد لديك مواد بعد" action-href="{{ url('/courses') }}" action-label="تصفح المواد المتاحة" />
        </x-card>
    @else
        <x-reveal stagger class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                <div class="nageeb-reveal-item">
                    <x-course-card
                        variant="student"
                        :title="$course->title"
                        :teacher="$course->teacher->name"
                        :image="\App\Support\NageebVisual::courseCover($course, $loop->index)"
                        :avatar="\App\Support\NageebVisual::teacherPhoto($course->teacher)"
                        :grade="$course->grade_level?->label()"
                        :href="route('student.my-courses.show', $course)"
                        cta="متابعة التعلّم"
                    />
                </div>
            @endforeach
        </x-reveal>
    @endif
</x-dashboard-layout>
@endsection
