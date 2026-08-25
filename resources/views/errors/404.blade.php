@extends('layouts.app')

@section('title', 'الصفحة غير موجودة — نجيب')

@section('content')
<x-public-layout>
    <div class="nageeb-card max-w-lg mx-auto text-center">
        <p class="text-4xl font-bold mb-3">404</p>
        <h1 class="nageeb-title-section mb-3">الصفحة غير موجودة</h1>
        <p class="nageeb-text-muted mb-6">الرابط الذي فتحته غير صحيح أو لم يعد متاحاً.</p>
        <a href="{{ url('/') }}" class="nageeb-btn nageeb-btn--primary">الصفحة الرئيسية</a>
    </div>
</x-public-layout>
@endsection
