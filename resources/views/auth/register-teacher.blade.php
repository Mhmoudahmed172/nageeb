@extends('layouts.app')

@section('title', 'تسجيل معلّم — نجيب')

@section('content')
<x-auth-layout title="تسجيل حساب معلّم" subtitle="شارك خبرتك التعليمية مع الطلاب">
    <div class="nageeb-alert nageeb-alert--info mb-5">
        سيتم مراجعة حسابك من قبل الإدارة قبل التفعيل الكامل.
    </div>

    <form method="POST" action="{{ route('register.teacher') }}" class="space-y-5">
        @csrf

        <x-form-input label="الاسم الكامل" name="name" required autofocus />

        <x-form-input label="البريد الإلكتروني" name="email" type="email" required />

        <x-form-input label="رقم الجوال" name="phone" type="tel" required />

        <x-form-input label="التخصص" name="specialization" required placeholder="مثال: رياضيات، فيزياء..." />

        <x-form-input label="كلمة المرور" name="password" type="password" required />

        <x-form-input label="تأكيد كلمة المرور" name="password_confirmation" type="password" required />

        <button type="submit" class="nageeb-btn nageeb-btn--primary w-full">
            إنشاء حساب معلّم
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
