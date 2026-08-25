@extends('layouts.app')

@section('title', 'طلبات السحب — نجيب')

@section('content')
<x-dashboard-layout title="طلبات السحب" role-label="المدير" active-menu="payouts">
    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <div class="nageeb-card nageeb-table-wrap">
        @if ($payouts->isEmpty())
            <x-empty-state title="لا توجد طلبات سحب معلّقة." />
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">المعلّم</th>
                        <th class="py-3 px-2 font-medium">المبلغ</th>
                        <th class="py-3 px-2 font-medium">بيانات الحساب</th>
                        <th class="py-3 px-2 font-medium">التاريخ</th>
                        <th class="py-3 px-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payouts as $payout)
                        <tr class="border-b border-border last:border-0 align-top">
                            <td class="py-3 px-2">{{ $payout->teacher->name }}</td>
                            <td class="py-3 px-2 price">{{ number_format($payout->amount, 2) }} ₪</td>
                            <td class="py-3 px-2 whitespace-pre-line">{{ $payout->bank_details }}</td>
                            <td class="py-3 px-2">{{ $payout->created_at->format('Y/m/d') }}</td>
                            <td class="py-3 px-2">
                                <form method="POST" action="{{ route('admin.payouts.settle', $payout) }}">
                                    @csrf
                                    <button type="submit" class="nageeb-btn nageeb-btn--primary text-sm py-2 px-3">تعليم كمسوّى</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dashboard-layout>
@endsection
