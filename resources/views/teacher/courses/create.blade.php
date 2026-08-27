@extends('layouts.app')

@section('title', 'إضافة مادة — نجيب')

@section('content')
<x-dashboard-layout title="إضافة مادة" role-label="المعلّم" active-menu="courses">
    @include('teacher.courses._form', ['course' => null])
</x-dashboard-layout>
@endsection
