@php
    $roleLabel = auth()->user()->role->label();
    $activeMenu = 'settings';
@endphp

@extends('layouts.app')

@section('title', 'إعدادات الحساب — نجيب')

@section('content')
<x-dashboard-layout title="إعدادات الحساب" :role-label="$roleLabel" active-menu="settings">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route(auth()->user()->isTeacher() ? 'teacher.settings.update' : 'student.settings.update') }}" class="nageeb-card max-w-xl grid gap-5 mb-8">
        @csrf
        @method('PUT')
        <h2 class="nageeb-title-section">البيانات الأساسية</h2>
        <x-form-input label="الاسم" name="name" required :value="$user->name" />
        <x-form-input label="البريد الإلكتروني" name="email" type="email" required :value="$user->email" />
        <x-form-input label="الجوال" name="phone" required :value="$user->phone" />
        <button type="submit" class="nageeb-btn nageeb-btn--primary justify-self-start">حفظ</button>
    </form>

    <form method="POST" action="{{ route(auth()->user()->isTeacher() ? 'teacher.settings.password' : 'student.settings.password') }}" class="nageeb-card max-w-xl grid gap-5">
        @csrf
        @method('PUT')
        <h2 class="nageeb-title-section">كلمة المرور</h2>
        <x-form-input label="كلمة المرور الحالية" name="current_password" type="password" required />
        <x-form-input label="كلمة المرور الجديدة" name="password" type="password" required />
        <x-form-input label="تأكيد كلمة المرور" name="password_confirmation" type="password" required />
        <button type="submit" class="nageeb-btn nageeb-btn--primary justify-self-start">تحديث كلمة المرور</button>
    </form>
</x-dashboard-layout>
@endsection
