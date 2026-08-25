@extends('layouts.app')

@section('title', 'تسجيل الدخول — نجيب')

@section('content')
<x-auth-layout title="تسجيل الدخول" subtitle="مرحباً بعودتك إلى نجيب">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-5">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-form-input label="البريد الإلكتروني" name="email" type="email" required autofocus />

        <x-form-input label="كلمة المرور" name="password" type="password" required />

        <label class="flex items-center gap-2 text-sm nageeb-text-muted cursor-pointer">
            <input type="checkbox" name="remember" class="nageeb-checkbox" />
            تذكّرني
        </label>

        <button type="submit" class="nageeb-btn nageeb-btn--primary w-full">
            دخول
        </button>
    </form>

    <x-slot:footer>
        <p class="mb-2">ليس لديك حساب؟</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('register.student') }}" class="text-primary font-medium hover:underline">
                تسجيل كطالب
            </a>
            <span class="hidden sm:inline nageeb-text-dim">|</span>
            <a href="{{ route('register.teacher') }}" class="text-primary font-medium hover:underline">
                تسجيل كمعلّم
            </a>
        </div>
    </x-slot:footer>
</x-auth-layout>
@endsection
