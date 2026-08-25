@extends('layouts.app')

@section('title', 'الأرباح — نجيب')

@section('content')
<x-dashboard-layout title="الأرباح" role-label="المعلّم" active-menu="earnings">
    <div class="grid gap-4 sm:grid-cols-2 mb-8">
        <div class="nageeb-card">
            <p class="nageeb-text-dim text-sm mb-1">إجمالي الأرباح</p>
            <p class="text-2xl font-bold price">{{ number_format($total, 2) }} ₪</p>
        </div>
        <div class="nageeb-card">
            <p class="nageeb-text-dim text-sm mb-1">أرباح هذا الشهر</p>
            <p class="text-2xl font-bold price">{{ number_format($monthTotal, 2) }} ₪</p>
        </div>
    </div>

    <div class="nageeb-card nageeb-table-wrap">
        <h2 class="nageeb-title-section mb-4">تفاصيل العمليات</h2>
        @if ($rows->isEmpty())
            <x-empty-state title="لا توجد عمليات بعد." />
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">الطالب</th>
                        <th class="py-3 px-2 font-medium">المادة</th>
                        <th class="py-3 px-2 font-medium">التاريخ</th>
                        <th class="py-3 px-2 font-medium">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-2">{{ $row['enrollment']->student->name }}</td>
                            <td class="py-3 px-2">{{ $row['enrollment']->course->title }}</td>
                            <td class="py-3 px-2">{{ $row['enrollment']->granted_at?->format('Y/m/d') }}</td>
                            <td class="py-3 px-2 price">{{ number_format($row['amount'], 2) }} ₪</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dashboard-layout>
@endsection
