@extends('layouts.app')

@section('title', 'غير مصرّح — نجيب')

@section('content')
<x-public-layout>
    <x-empty-state title="غير مصرّح لك بالوصول" image="illustrations/empty.png" action-href="{{ url('/') }}" action-label="الصفحة الرئيسية">
        لا يمكنك فتح هذه الصفحة. إن كنت تظن أن هذا خطأ، عد إلى لوحتك أو الصفحة الرئيسية.
    </x-empty-state>
    @auth
        <div class="text-center mt-4">
            <a href="{{ auth()->user()->dashboardRoute() }}" class="nageeb-btn nageeb-btn--outline">العودة للوحة التحكم</a>
        </div>
    @endauth
</x-public-layout>
@endsection
