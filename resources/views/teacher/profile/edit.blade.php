@extends('layouts.app')

@section('title', 'ملف المدرّس — نجيب')

@section('content')
<x-dashboard-layout title="ملف المدرّس" role-label="المعلّم" active-menu="profile">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('teacher.profile.update') }}" enctype="multipart/form-data" class="nageeb-card max-w-xl grid gap-5">
        @csrf
        @method('PUT')
        @if ($profile?->avatarSrc())
            <img src="{{ $profile->avatarSrc() }}" alt="" class="w-24 h-24 object-cover rounded-full">
        @endif
        <div class="nageeb-field">
            <label for="avatar" class="nageeb-label">الصورة الشخصية</label>
            <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png" class="nageeb-input">
            @error('avatar')
                <p class="nageeb-field-error">{{ $message }}</p>
            @enderror
        </div>
        <x-form-input label="التخصص" name="specialization" :value="$profile?->specialization" />
        <x-form-textarea label="نبذة" name="bio" :value="$profile?->bio" rows="5" />
        <button type="submit" class="nageeb-btn nageeb-btn--primary justify-self-start">حفظ</button>
    </form>
</x-dashboard-layout>
@endsection
