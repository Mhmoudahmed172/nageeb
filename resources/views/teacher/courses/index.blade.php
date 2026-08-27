@extends('layouts.app')

@section('title', 'مقرراتي — نجيب')

@section('content')
<x-dashboard-layout title="مقرراتي" role-label="المعلّم" active-menu="courses">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <p class="nageeb-text-muted">أدر موادك ثم أنشئ خطط الوصول والأسعار الخاصة بكل مادة.</p>
        <a href="{{ route('teacher.courses.create') }}" class="nageeb-btn nageeb-btn--primary self-start">إضافة مادة</a>
    </div>

    <div class="nageeb-card nageeb-table-wrap">
        @if ($courses->isEmpty())
            <x-empty-state title="لا توجد مواد بعد." action-href="{{ route('teacher.courses.create') }}" action-label="إنشاء مادة" />
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">المادة</th>
                        <th class="py-3 px-2 font-medium">الحالة</th>
                        <th class="py-3 px-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courses as $course)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-2">{{ $course->title }}</td>
                            <td class="py-3 px-2">{{ $course->status->label() }}</td>
                            <td class="py-3 px-2">
                                <div class="flex flex-wrap gap-2">
                                <a href="{{ route('teacher.courses.content', $course) }}" class="nageeb-btn nageeb-btn--primary text-sm py-2 px-3">إدارة</a>
                                <a href="{{ route('teacher.courses.edit', $course) }}" class="nageeb-btn nageeb-btn--outline text-sm py-2 px-3">تعديل</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dashboard-layout>
@endsection
