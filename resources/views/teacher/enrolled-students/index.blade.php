@extends('layouts.app')

@section('title', 'الملتحقون — نجيب')

@section('content')
<x-dashboard-layout title="الملتحقون" role-label="المعلّم" active-menu="enrollments">
    <form method="GET" action="{{ route('teacher.enrollments.index') }}" class="nageeb-card mb-6 grid gap-4 sm:grid-cols-4">
        <x-form-input label="بحث بالاسم" name="search" :value="$search" />
        <x-form-select label="المادة" name="course_id">
            <option value="">كل المواد</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected((string) $courseId === (string) $course->id)>{{ $course->title }}</option>
            @endforeach
        </x-form-select>
        <x-form-select label="المنطقة" name="region">
            <option value="">كل المناطق</option>
            @foreach ($regions as $option)
                <option value="{{ $option->id }}" @selected((string) $region === (string) $option->id || $region === $option->code)>{{ $option->name }}</option>
            @endforeach
        </x-form-select>
        <div class="flex items-end">
            <button type="submit" class="nageeb-btn nageeb-btn--primary w-full">تصفية</button>
        </div>
    </form>

    <div class="nageeb-card nageeb-table-wrap">
        @if ($enrollments->isEmpty())
            <x-empty-state title="لا يوجد ملتحقون مطابقون." />
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">اسم الطالب</th>
                        <th class="py-3 px-2 font-medium">المنطقة</th>
                        <th class="py-3 px-2 font-medium">المادة</th>
                        <th class="py-3 px-2 font-medium">تاريخ الالتحاق</th>
                        <th class="py-3 px-2 font-medium">تاريخ الانتهاء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($enrollments as $enrollment)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-2">{{ $enrollment->student->name }}</td>
                            <td class="py-3 px-2">{{ $enrollment->student->studentProfile?->region?->name ?? '—' }}</td>
                            <td class="py-3 px-2">{{ $enrollment->course->title }}</td>
                            <td class="py-3 px-2">{{ $enrollment->granted_at?->format('Y/m/d') }}</td>
                            <td class="py-3 px-2">{{ $enrollment->expires_at?->format('Y/m/d') ?? 'غير محدد' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dashboard-layout>
@endsection
