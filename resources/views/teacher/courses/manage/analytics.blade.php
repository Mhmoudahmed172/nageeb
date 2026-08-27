@extends('layouts.app')

@section('title', $course->title.' — التحليلات')

@section('content')
<x-course-workspace :course="$course" active="analytics">
    <p class="nageeb-text-muted">تحليلات هذه المادة ستُضاف لاحقاً. يمكنك متابعة الأرقام العامة من لوحة الأداء.</p>
</x-course-workspace>
@endsection
