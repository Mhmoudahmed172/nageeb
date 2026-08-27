@extends('layouts.app')

@section('title', $course->title.' — الإعدادات')

@section('content')
<x-course-workspace :course="$course" active="settings">
    <div class="grid gap-6 max-w-xl">
        <p class="nageeb-text-muted">عدّل بيانات المادة أو ألغِ نشرها من شريط الإجراءات أعلاه.</p>
        <a href="{{ route('teacher.courses.edit', $course) }}" class="nageeb-btn nageeb-btn--primary self-start">تعديل المادة</a>
        <form method="POST" action="{{ route('teacher.courses.destroy', $course) }}" onsubmit="return confirm('حذف المادة نهائياً مع وحداتها ودروسها؟')">
            @csrf
            @method('DELETE')
            <button type="submit" class="nageeb-btn nageeb-btn--outline">حذف المادة</button>
        </form>
    </div>
</x-course-workspace>
@endsection
