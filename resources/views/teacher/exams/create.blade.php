@extends('layouts.app')

@section('title', 'إنشاء اختبار — نجيب')

@section('content')
<x-dashboard-layout title="إنشاء اختبار" role-label="المعلّم" active-menu="quizzes">
    @include('teacher.exams._alerts')

    @if ($courses->isEmpty())
        <x-empty-state
            title="أنشئ مادة أولًا"
            action-href="{{ route('teacher.courses.create') }}"
            action-label="+ إضافة مادة"
        >
            الاختبار يجب أن يرتبط بمادة من موادك.
        </x-empty-state>
    @else
        @include('teacher.exams._form', ['courses' => $courses, 'regions' => $regions, 'exam' => null])
    @endif
</x-dashboard-layout>
@endsection
