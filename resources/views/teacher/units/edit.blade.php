@extends('layouts.app')

@section('title', 'تعديل الوحدة — نجيب')

@section('content')
<x-course-workspace :course="$course" active="content">
    @include('teacher.units._form', ['course' => $course, 'unit' => $unit])
</x-course-workspace>
@endsection
