@extends('layouts.app')

@section('title', 'التفاعل — نجيب')

@section('content')
<x-dashboard-layout title="التفاعل" role-label="المعلّم" active-menu="engagement">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    @forelse ($questions as $question)
        <article class="nageeb-card mb-4">
            <p class="text-sm nageeb-text-dim mb-1">{{ $question->created_at->format('Y/m/d H:i') }}</p>
            <p class="font-medium">{{ $question->user->name }}</p>
            <p class="nageeb-text-muted text-sm mb-2">الدرس: {{ $question->lesson->title }} — {{ $question->lesson->unit->course->title }}</p>
            <p class="mb-4">{{ $question->message }}</p>

            @foreach ($question->replies as $reply)
                <div class="me-4 mb-2 p-3 bg-primary-muted">
                    <p class="text-sm font-medium">{{ $reply->user->name }}</p>
                    <p class="text-sm">{{ $reply->message }}</p>
                </div>
            @endforeach

            @if ($question->replies->isEmpty())
                <form method="POST" action="{{ route('teacher.interactions.reply', $question) }}" class="grid gap-3 max-w-xl">
                    @csrf
                    <textarea name="message" class="nageeb-input" rows="2" required placeholder="اكتب ردك">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="nageeb-field-error" role="alert">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="nageeb-btn nageeb-btn--primary justify-self-start text-sm">إرسال الرد</button>
                </form>
            @endif
        </article>
    @empty
        <div class="nageeb-card">
            <x-empty-state title="لا توجد أسئلة بعد." />
        </div>
    @endforelse
</x-dashboard-layout>
@endsection
