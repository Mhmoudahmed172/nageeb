@extends('layouts.app')

@section('title', 'إضافة فصل — نجيب')

@section('content')
<x-course-workspace :course="$course" active="content">
    @include('teacher.semesters._form', ['course' => $course, 'semester' => null])
</x-course-workspace>
@endsection
