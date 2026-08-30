@extends('layouts.app')

@section('title', 'تعديل الاختبار — نجيب')

@section('content')
<x-dashboard-layout title="تعديل الاختبار" role-label="المعلّم" active-menu="quizzes">
    @include('teacher.exams._alerts')

    @include('teacher.exams._form', ['courses' => $courses, 'regions' => $regions, 'exam' => $exam])
</x-dashboard-layout>
@endsection
