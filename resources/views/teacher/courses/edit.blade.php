@extends('layouts.app')

@section('title', 'تعديل المادة — نجيب')

@section('content')
<x-course-workspace :course="$course" active="settings">
    @include('teacher.courses._form', ['course' => $course])
</x-course-workspace>
@endsection
