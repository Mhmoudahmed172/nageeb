@extends('layouts.app')

@section('title', 'باقات الاشتراك — نجيب')

@section('content')
<x-dashboard-layout title="باقات الاشتراك — {{ $course->title }}" role-label="المعلّم" active-menu="packages">
    @include('teacher.courses._tabs', ['course' => $course, 'active' => 'packages'])

    @if (session('status'))
        <div class="nageeb-alert nageeb-alert--success mb-6">{{ session('status') }}</div>
    @endif

    <div class="nageeb-card max-w-4xl mb-8">
        <h2 class="nageeb-title-section mb-4">إضافة باقة جديدة</h2>
        <p class="nageeb-text-muted text-sm mb-5">يمكنك إضافة أكثر من باقة لنفس المادة (مثلاً: الفصل الأول، الفصل الثاني، باقة سنوية).</p>
        <form method="POST" action="{{ route('teacher.courses.packages.store', $course) }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <x-form-input label="اسم الباقة" name="name" required />
            <x-form-input label="مدة الباقة" name="duration_label" required />
            <x-form-input label="سعر غزة" name="price_gaza" type="number" required step="0.01" min="0" />
            <x-form-input label="سعر الضفة والخارج" name="price_west_bank_abroad" type="number" required step="0.01" min="0" />
            <div class="sm:col-span-2">
                <button type="submit" class="nageeb-btn nageeb-btn--primary">إضافة الباقة</button>
            </div>
        </form>
    </div>

    <div class="nageeb-card overflow-x-auto" x-data="{ editing: null }">
        <h2 class="nageeb-title-section mb-4">باقات هذه المادة</h2>
        @if ($packages->isEmpty())
            <x-empty-state title="لا توجد باقات بعد.">
                أضف باقة من النموذج أعلاه (مثلاً: الفصل الأول أو باقة سنوية).
            </x-empty-state>
        @else
            <table class="w-full text-sm text-start">
                <thead>
                    <tr class="border-b border-border">
                        <th class="py-3 px-2 font-medium">الاسم</th>
                        <th class="py-3 px-2 font-medium">سعر غزة</th>
                        <th class="py-3 px-2 font-medium">سعر الضفة والخارج</th>
                        <th class="py-3 px-2 font-medium">مدة الباقة</th>
                        <th class="py-3 px-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($packages as $package)
                        <tr class="border-b border-border last:border-0 align-top">
                            <td class="py-3 px-2" colspan="5">
                                <div x-show="editing !== {{ $package->id }}" class="flex flex-wrap items-center gap-3 justify-between">
                                    <div class="grid sm:grid-cols-4 gap-3 flex-1">
                                        <span>{{ $package->name }}</span>
                                        <span>{{ number_format($package->price_gaza, 2) }}</span>
                                        <span>{{ number_format($package->price_west_bank_abroad, 2) }}</span>
                                        <span>{{ $package->duration_label }}</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" class="nageeb-btn nageeb-btn--outline text-sm py-2 px-3" @click="editing = {{ $package->id }}">تعديل</button>
                                        <form method="POST" action="{{ route('teacher.courses.packages.destroy', [$course, $package]) }}" onsubmit="return confirm('حذف هذه الباقة؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="nageeb-btn nageeb-btn--secondary text-sm py-2 px-3">حذف</button>
                                        </form>
                                    </div>
                                </div>
                                <form
                                    method="POST"
                                    action="{{ route('teacher.courses.packages.update', [$course, $package]) }}"
                                    class="grid gap-3 sm:grid-cols-2"
                                    x-show="editing === {{ $package->id }}"
                                    x-cloak
                                >
                                    @csrf
                                    @method('PUT')
                                    <x-form-input :id="'pkg-name-'.$package->id" label="اسم الباقة" name="name" required :value="$package->name" />
                                    <x-form-input :id="'pkg-duration-'.$package->id" label="مدة الباقة" name="duration_label" required :value="$package->duration_label" />
                                    <x-form-input :id="'pkg-gaza-'.$package->id" label="سعر غزة" name="price_gaza" type="number" required step="0.01" min="0" :value="$package->price_gaza" />
                                    <x-form-input :id="'pkg-wb-'.$package->id" label="سعر الضفة والخارج" name="price_west_bank_abroad" type="number" required step="0.01" min="0" :value="$package->price_west_bank_abroad" />
                                    <div class="sm:col-span-2 flex gap-2">
                                        <button type="submit" class="nageeb-btn nageeb-btn--primary text-sm">حفظ</button>
                                        <button type="button" class="nageeb-btn nageeb-btn--outline text-sm" @click="editing = null">إلغاء</button>
                                    </div>
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
