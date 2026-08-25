@extends('layouts.app')

@section('title', 'تعديل المادة — نجيب')

@section('content')
<x-dashboard-layout title="تعديل المادة" role-label="المعلّم" active-menu="courses">
    @include('teacher.courses._tabs', ['course' => $course, 'active' => 'details'])

    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('teacher.courses.update', $course) }}" class="nageeb-card max-w-2xl grid gap-5">
        @csrf
        @method('PUT')
        <x-form-input label="اسم المادة" name="title" required :value="$course->title" />
        <x-form-textarea label="الوصف" name="description" :value="$course->description" />
        <x-form-input label="الصف" name="grade_level" :value="$course->grade_level" />
        <x-form-select label="الحالة" name="status" required>
            @foreach (\App\Enums\CourseStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', $course->status->value) === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </x-form-select>
        <button type="submit" class="nageeb-btn nageeb-btn--primary justify-self-start">حفظ</button>
    </form>
</x-dashboard-layout>
@endsection
