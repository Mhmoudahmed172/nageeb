@extends('layouts.app')

@section('title', 'تسجيل طالب — نجيب')

@section('content')
<x-auth-layout title="تسجيل حساب طالب" subtitle="انضم كطالب وابدأ رحلة التعلّم">
    <form method="POST" action="{{ route('register.student') }}" class="space-y-5">
        @csrf

        <x-form-input label="الاسم الكامل" name="name" required autofocus />

        <x-form-input label="البريد الإلكتروني" name="email" type="email" required />

        <x-form-input label="رقم الجوال" name="phone" type="tel" required />

        <x-form-select label="المنطقة" name="region" required>
            <option value="" disabled @selected(old('region') === null)>اختر المنطقة</option>
            @foreach (\App\Enums\StudentRegion::cases() as $region)
                <option value="{{ $region->value }}" @selected(old('region') === $region->value)>
                    {{ $region->label() }}
                </option>
            @endforeach
        </x-form-select>

        <x-form-input label="كلمة المرور" name="password" type="password" required />

        <x-form-input label="تأكيد كلمة المرور" name="password_confirmation" type="password" required />

        <button type="submit" class="nageeb-btn nageeb-btn--primary w-full">
            إنشاء حساب طالب
        </button>
    </form>

    <x-slot:footer>
        لديك حساب بالفعل؟
        <a href="{{ route('login') }}" class="text-primary font-medium hover:underline ms-1">
            تسجيل الدخول
        </a>
    </x-slot:footer>
</x-auth-layout>
@endsection
