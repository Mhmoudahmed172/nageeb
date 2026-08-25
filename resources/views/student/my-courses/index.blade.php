@extends('layouts.app')

@section('title', 'موادي — نجيب')

@section('content')
<x-dashboard-layout title="موادي" role-label="الطالب" active-menu="courses">
    @if ($courses->isEmpty())
        <div class="nageeb-card max-w-xl">
            <x-empty-state title="لا يوجد لديك مواد بعد" action-href="{{ url('/courses') }}" action-label="تصفح المواد المتاحة" />
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                <a href="{{ route('student.my-courses.show', $course) }}" class="nageeb-card hover:shadow-md block">
                    <h2 class="font-semibold mb-2">{{ $course->title }}</h2>
                    <p class="nageeb-text-muted text-sm">{{ $course->teacher->name }}</p>
                </a>
            @endforeach
        </div>
    @endif
</x-dashboard-layout>
@endsection
