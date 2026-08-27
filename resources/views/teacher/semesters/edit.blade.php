@extends('layouts.app')

@section('title', 'تعديل فصل — نجيب')

@section('content')
<x-course-workspace :course="$course" active="content">
    @include('teacher.semesters._form', ['course' => $course, 'semester' => $semester])
</x-course-workspace>
@endsection
