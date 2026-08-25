@extends('layouts.app')

@section('title', 'المعلمون — نجيب')

@section('content')
<x-dashboard-layout title="المعلمون" role-label="المدير" active-menu="teachers">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <div class="nageeb-card nageeb-table-wrap">
        @if ($teachers->isEmpty())
            <x-empty-state title="لا يوجد معلمون بعد." />
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">الاسم</th>
                        <th class="py-3 px-2 font-medium">البريد</th>
                        <th class="py-3 px-2 font-medium">التخصص</th>
                        <th class="py-3 px-2 font-medium">التوثيق</th>
                        <th class="py-3 px-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teachers as $teacher)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-2">{{ $teacher->name }}</td>
                            <td class="py-3 px-2">{{ $teacher->email }}</td>
                            <td class="py-3 px-2">{{ $teacher->teacherProfile?->specialization ?? '—' }}</td>
                            <td class="py-3 px-2">
                                @if ($teacher->teacherProfile?->is_verified)
                                    <span class="nageeb-badge nageeb-badge--support">موثّق</span>
                                @else
                                    <span class="nageeb-badge nageeb-badge--secondary">غير موثّق</span>
                                @endif
                            </td>
                            <td class="py-3 px-2">
                                @unless ($teacher->teacherProfile?->is_verified)
                                    <form method="POST" action="{{ route('admin.teachers.verify', $teacher) }}">
                                        @csrf
                                        <button type="submit" class="nageeb-btn nageeb-btn--primary text-sm py-2 px-3">توثيق</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dashboard-layout>
@endsection
