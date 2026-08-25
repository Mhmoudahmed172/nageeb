@extends('layouts.app')

@section('title', 'غير مصرّح — نجيب')

@section('content')
<x-public-layout>
    <div class="nageeb-card max-w-lg mx-auto text-center">
        <p class="text-4xl font-bold mb-3">403</p>
        <h1 class="nageeb-title-section mb-3">غير مصرّح لك بالوصول</h1>
        <p class="nageeb-text-muted mb-6">لا يمكنك فتح هذه الصفحة. إن كنت تظن أن هذا خطأ، عد إلى لوحتك أو الصفحة الرئيسية.</p>
        <div class="flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ auth()->user()->dashboardRoute() }}" class="nageeb-btn nageeb-btn--primary">العودة للوحة التحكم</a>
            @endauth
            <a href="{{ url('/') }}" class="nageeb-btn nageeb-btn--outline">الصفحة الرئيسية</a>
        </div>
    </div>
</x-public-layout>
@endsection
