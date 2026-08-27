@extends('layouts.app')

@section('title', 'إضافة درس — نجيب')

@section('content')
<x-course-workspace :course="$course" active="content">
    @if ($needsUnit)
        <x-empty-state
            title="يجب إنشاء وحدة أولًا"
            action-href="{{ route('teacher.courses.units.create', $course) }}"
            action-label="+ إضافة وحدة"
        >
            أضف وحدة إلى هذه المادة قبل إضافة الدروس.
        </x-empty-state>
    @else
        @include('teacher.lessons._form', [
            'course' => $course,
            'selectedUnitId' => $selectedUnitId,
        ])
    @endif
</x-course-workspace>
@endsection
