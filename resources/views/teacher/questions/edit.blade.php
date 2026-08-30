@extends('layouts.app')

@section('title', 'تعديل السؤال — نجيب')

@section('content')
<x-dashboard-layout title="تعديل السؤال" role-label="المعلّم" active-menu="questions">
    @include('teacher.exams._alerts')

    @include('teacher.questions._form', [
        'courses' => $courses,
        'types' => $types,
        'difficulties' => $difficulties,
        'question' => $question,
    ])
</x-dashboard-layout>
@endsection
