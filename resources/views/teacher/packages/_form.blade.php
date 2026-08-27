@php
    $selectedSemesters = old('semester_ids', $editingPlan?->semesters->pluck('id')->all() ?? []);
    $currentStatus = old('status', $editingPlan?->status->value ?? \App\Enums\ContentStatus::Live->value);
@endphp

<div class="grid sm:grid-cols-2 gap-5">
    <x-form-input
        label="اسم الخطة"
        name="title"
        :id="$formKey.'-title'"
        :value="$editingPlan?->title"
        placeholder="مثال: الفصل الأول"
        required
    />
    <x-form-select label="الحالة" name="status" :id="$formKey.'-status'" required>
        @foreach (\App\Enums\ContentStatus::cases() as $status)
            <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </x-form-select>
    <div class="sm:col-span-2">
        <x-form-textarea
            label="الوصف"
            name="description"
            :id="$formKey.'-description'"
            :value="$editingPlan?->description"
            rows="3"
            placeholder="اشرح للطالب ما الذي تتضمنه هذه الخطة."
        />
    </div>
    <x-form-input
        label="مدة الوصول"
        name="access_duration_days"
        :id="$formKey.'-duration'"
        type="number"
        min="1"
        :value="$editingPlan?->access_duration_days"
        help="بالأيام. اتركه فارغاً لوصول غير محدود."
    />
</div>

<fieldset>
    <legend class="nageeb-heading-3">الفصول التي يحصل الطالب عليها</legend>
    <p class="nageeb-text-muted text-sm mt-1 mb-3">اختر فصلاً واحداً أو أكثر ضمن هذه المادة.</p>
    <div class="grid sm:grid-cols-2 gap-2">
        @foreach ($course->semesters as $semester)
            <label class="access-plan-choice">
                <input
                    type="checkbox"
                    name="semester_ids[]"
                    value="{{ $semester->id }}"
                    @checked(in_array($semester->id, $selectedSemesters))
                >
                <span>
                    <strong>{{ $semester->title }}</strong>
                    <small>{{ $semester->units->count() }} وحدة</small>
                </span>
            </label>
        @endforeach
    </div>
    @error('semester_ids')<p class="nageeb-field-error mt-2">{{ $message }}</p>@enderror
</fieldset>

<fieldset>
    <legend class="nageeb-heading-3">السعر حسب منطقة الطالب</legend>
    <p class="nageeb-text-muted text-sm mt-1 mb-3">يظهر للطالب السعر المرتبط بمنطقته المسجلة.</p>
    <div class="grid sm:grid-cols-2 gap-3">
        @foreach ($regions as $index => $region)
            @php($currentPrice = $editingPlan?->prices->firstWhere('region_id', $region->id))
            <div class="access-plan-price">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div>
                        <p class="font-bold">{{ $region->name }}</p>
                        <p class="nageeb-caption">{{ $region->code }}</p>
                    </div>
                    <x-badge variant="info">ILS</x-badge>
                </div>
                <input type="hidden" name="prices[{{ $index }}][region_id]" value="{{ $region->id }}">
                <x-price-input
                    label="السعر"
                    :name="'prices['.$index.'][price]'"
                    :id="$formKey.'-price-'.$region->id"
                    :value="$currentPrice?->price"
                    required
                />
                <input type="hidden" name="prices[{{ $index }}][currency]" value="ILS">
            </div>
        @endforeach
    </div>
    @error('prices')<p class="nageeb-field-error mt-2">{{ $message }}</p>@enderror
</fieldset>
