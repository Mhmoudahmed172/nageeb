@extends('layouts.app')

@section('title', $course->title.' — الطلاب')

@section('content')
<x-course-workspace :course="$course" active="students">
    @if ($course->enrollments->isEmpty())
        <x-empty-state title="لا طلاب ملتحقون بهذه المادة بعد." />
    @else
        <ul class="grid gap-3">
            @foreach ($course->enrollments as $enrollment)
                <li class="flex flex-wrap justify-between gap-2 py-3 border-b border-border">
                    <span>{{ $enrollment->student->name }}</span>
                    <span class="text-sm nageeb-text-dim">{{ $enrollment->granted_at?->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</x-course-workspace>
@endsection
