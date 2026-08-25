@extends('layouts.app')

@section('title', 'نظرة عامة — نجيب')

@section('content')
<x-dashboard-layout title="نظرة عامة" role-label="المدير" active-menu="dashboard">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="nageeb-card">
            <p class="nageeb-text-dim text-sm mb-1">إجمالي المعلمين</p>
            <p class="text-2xl font-bold">{{ $teachersCount }}</p>
        </div>
        <div class="nageeb-card">
            <p class="nageeb-text-dim text-sm mb-1">إجمالي الطلاب</p>
            <p class="text-2xl font-bold">{{ $studentsCount }}</p>
        </div>
        <div class="nageeb-card">
            <p class="nageeb-text-dim text-sm mb-1">إجمالي المواد</p>
            <p class="text-2xl font-bold">{{ $coursesCount }}</p>
        </div>
        <div class="nageeb-card">
            <p class="nageeb-text-dim text-sm mb-1">إجمالي الأرباح</p>
            <p class="text-2xl font-bold price">{{ number_format($totalEarnings, 2) }} ₪</p>
        </div>
    </div>
</x-dashboard-layout>
@endsection
