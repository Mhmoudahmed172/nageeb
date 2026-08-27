@extends('layouts.app')

@section('title', 'إضافة وحدة — نجيب')

@section('content')
<x-course-workspace :course="$course" active="content">
    @include('teacher.units._form', ['course' => $course, 'unit' => null, 'suggestedTitle' => $suggestedTitle])
</x-course-workspace>
@endsection
