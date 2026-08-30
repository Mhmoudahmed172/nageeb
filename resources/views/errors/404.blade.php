@extends('layouts.app')

@section('title', 'الصفحة غير موجودة — نجيب')

@section('content')
<x-public-layout>
    <x-empty-state title="الصفحة غير موجودة" image="illustrations/empty.png" action-href="{{ url('/') }}" action-label="الصفحة الرئيسية">
        الرابط الذي فتحته غير صحيح أو لم يعد متاحاً.
    </x-empty-state>
</x-public-layout>
@endsection
