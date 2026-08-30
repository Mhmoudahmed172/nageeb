@extends('layouts.app')

@section('title', 'إضافة سؤال — نجيب')

@section('content')
<x-dashboard-layout title="إضافة سؤال" role-label="المعلّم" active-menu="questions">
    @include('teacher.exams._alerts')

    @include('teacher.questions._form', [
        'courses' => $courses,
        'types' => $types,
        'difficulties' => $difficulties,
        'question' => null,
    ])
</x-dashboard-layout>
@endsection
