@extends('layouts.app')

@section('title', 'مادة جديدة — نجيب')

@section('content')
<x-dashboard-layout title="مادة جديدة" role-label="المعلّم" active-menu="courses">
    <form method="POST" action="{{ route('teacher.courses.store') }}" class="nageeb-card max-w-2xl grid gap-5">
        @csrf
        <x-form-input label="اسم المادة" name="title" required />
        <x-form-textarea label="الوصف" name="description" />
        <x-form-input label="الصف" name="grade_level" />
        <x-form-select label="الحالة" name="status" required>
            @foreach (\App\Enums\CourseStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(old('status', 'live') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </x-form-select>
        <button type="submit" class="nageeb-btn nageeb-btn--primary justify-self-start">حفظ المادة</button>
    </form>
</x-dashboard-layout>
@endsection
