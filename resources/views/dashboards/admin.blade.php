@extends('layouts.app')

@section('title', 'لوحة الإدارة — نجيب')

@section('content')
<x-dashboard-layout title="لوحة الإدارة" role-label="المدير">
    <div class="nageeb-card max-w-2xl">
        <h2 class="nageeb-title-section mb-2">مرحباً، {{ $user->name }}</h2>
        <p class="nageeb-text-muted mb-6">أنت مسجّل كمدير نظام.</p>

        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div>
                <dt class="nageeb-text-dim mb-1">البريد الإلكتروني</dt>
                <dd class="font-medium">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">رقم الجوال</dt>
                <dd class="font-medium">{{ $user->phone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="nageeb-text-dim mb-1">الدور</dt>
                <dd><span class="nageeb-badge nageeb-badge--primary">{{ $user->role->label() }}</span></dd>
            </div>
        </dl>
    </div>
</x-dashboard-layout>
@endsection
