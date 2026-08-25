@extends('layouts.app')

@section('title', 'تم إرسال الطلب — نجيب')

@section('content')
<x-dashboard-layout title="تم إرسال الطلب" role-label="الطالب" active-menu="courses">
    <div class="nageeb-card max-w-xl">
        <h2 class="nageeb-title-section mb-3">طلبك قيد المراجعة من المعلم</h2>
        <p class="nageeb-text-muted mb-6">تم استلام طلب اشتراكك في «{{ $course->title }}». سيراجعه المعلّم قريباً.</p>
        <a href="{{ route('student.dashboard') }}" class="nageeb-btn nageeb-btn--primary">عودة للوحة الطالب</a>
    </div>
</x-dashboard-layout>
@endsection
