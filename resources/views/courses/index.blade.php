@extends('layouts.app')

@section('title', 'المواد المتاحة — نجيب')

@section('content')
<x-public-layout title="المواد المتاحة">
    @if ($courses->isEmpty())
        <x-empty-state title="لا توجد مواد منشورة حالياً." />
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($courses as $course)
                <article class="nageeb-card">
                    <h2 class="font-semibold mb-2">{{ $course->title }}</h2>
                    <p class="nageeb-text-muted text-sm mb-4">{{ $course->teacher->name }}</p>
                    <a href="{{ route('courses.subscribe', $course) }}" class="nageeb-btn nageeb-btn--primary text-sm">الاشتراك</a>
                </article>
            @endforeach
        </div>
    @endif
</x-public-layout>
@endsection
