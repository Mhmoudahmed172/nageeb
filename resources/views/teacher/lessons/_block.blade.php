@php
    use App\Enums\ContentStatus;
    use App\Enums\LessonContentType;
    use App\Support\VideoAsset;

    $type = $content->type;
    $isMedia = in_array($type, [LessonContentType::Video, LessonContentType::Audio], true);
    $icon = match ($type) {
        LessonContentType::Video => 'video',
        LessonContentType::Text => 'text',
        LessonContentType::File => 'file',
        LessonContentType::Audio => 'audio',
        LessonContentType::Link => 'link',
        LessonContentType::Quiz => 'quiz',
        LessonContentType::Assignment => 'assignment',
        LessonContentType::LiveSession => 'live',
    };
    $scope = $content->regions->isNotEmpty() ? 'selected' : 'all';
    $regionSummary = $content->regions->isNotEmpty()
        ? $content->regions->pluck('name')->join('، ')
        : 'جميع المناطق';
    $data = $content->data ?? [];
@endphp

<article
    class="lesson-block"
    data-block
    data-id="{{ $content->id }}"
    data-save-url="{{ route('teacher.courses.lesson-contents.update', [$course, $lesson, $content]) }}"
    x-data="{ open: {{ $index === 0 ? 'true' : 'false' }}, scope: '{{ $scope }}' }"
>
    <header class="lesson-block__head">
        <span class="drag-handle" aria-label="اسحب لترتيب المحتوى" title="اسحب للترتيب">
            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor"><circle cx="7" cy="5" r="1"/><circle cx="13" cy="5" r="1"/><circle cx="7" cy="10" r="1"/><circle cx="13" cy="10" r="1"/><circle cx="7" cy="15" r="1"/><circle cx="13" cy="15" r="1"/></svg>
        </span>
        <span class="lesson-block__index">{{ sprintf('%02d', $index + 1) }}</span>
        <span class="lesson-block__type"><x-nav-icon :name="$icon" class="size-4" />{{ $type->label() }}</span>

        <button type="button" class="lesson-block__title" @click="open = ! open" :aria-expanded="open ? 'true' : 'false'">
            <span class="truncate">{{ $content->displayName() }}</span>
        </button>

        <div class="lesson-block__chips">
            @if ($type === LessonContentType::Video)
                <x-badge variant="{{ VideoAsset::state($content) === VideoAsset::STATE_READY ? 'success' : 'warning' }}">
                    {{ VideoAsset::stateLabel($content) }}
                </x-badge>
                @if ($duration = VideoAsset::durationLabel($content))
                    <span class="nageeb-caption">{{ $duration }}</span>
                @endif
            @endif
            <x-badge variant="{{ $content->status === ContentStatus::Live ? 'success' : 'warning' }}">{{ $content->status->label() }}</x-badge>
            <span class="nageeb-caption lesson-block__regions">{{ $regionSummary }}</span>
        </div>

        <div class="lesson-block__tools">
            <button type="button" class="nageeb-btn nageeb-btn--ghost nageeb-btn--icon nageeb-btn--sm" @click="open = ! open" aria-label="فتح المحتوى">
                <svg class="size-4 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor"><path d="m5.5 7.5 4.5 4 4.5-4"/></svg>
            </button>
            <form
                method="POST"
                action="{{ route('teacher.courses.lesson-contents.destroy', [$course, $lesson, $content]) }}"
                onsubmit="return confirm('حذف هذا المحتوى نهائيًا؟')"
            >
                @csrf
                @method('DELETE')
                <button type="submit" class="nageeb-btn nageeb-btn--ghost nageeb-btn--icon nageeb-btn--sm text-danger" aria-label="حذف المحتوى">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M9 7V5h6v2M6 7l1 13h10l1-13M10 11v6M14 11v6"/></svg>
                </button>
            </form>
        </div>
    </header>

    <div class="lesson-block__body" x-show="open" x-cloak x-transition.opacity>
        <div class="lesson-block__fields">
            <div class="nageeb-field">
                <label class="nageeb-label" for="block-title-{{ $content->id }}">عنوان المحتوى</label>
                <input
                    id="block-title-{{ $content->id }}"
                    type="text"
                    name="title"
                    class="nageeb-input"
                    maxlength="255"
                    value="{{ $content->title }}"
                    placeholder="{{ $type->label() }}"
                    @input="scheduleSave($event)"
                >
            </div>

            @switch($type)
                @case(LessonContentType::Video)
                    <div class="lesson-block__media">
                        <div class="lesson-block__media-icon"><x-nav-icon name="video" class="size-6" /></div>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $data['original_name'] ?? $data['name'] ?? 'ملف الفيديو' }}</p>
                        <p class="nageeb-caption mt-1">
                            {{ VideoAsset::stateLabel($content) }}
                            @if ($duration = VideoAsset::durationLabel($content))
                                · المدة {{ $duration }}
                            @endif
                        </p>
                    </div>
                    @if ($content->accessUrl())
                        <video class="w-full mt-3" controls preload="metadata" src="{{ $content->accessUrl() }}"></video>
                    @endif
                    <p class="lesson-block__media-note">
                            يُعرض هذا الفيديو للطلاب عبر مشغل محمي داخل المنصة، ولا يتم إنشاء رابط عام له.
                        </p>
                    </div>
                    @break

                @case(LessonContentType::Audio)
                    <div class="lesson-block__media">
                        <div class="lesson-block__media-icon"><x-nav-icon name="audio" class="size-6" /></div>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $data['name'] ?? 'ملف صوتي' }}</p>
                            <p class="nageeb-caption mt-1">جاهز للتشغيل داخل المنصة</p>
                        </div>
                        <p class="lesson-block__media-note">يُشغَّل الملف الصوتي داخل المنصة فقط دون رابط عام.</p>
                    </div>
                    @break

                @case(LessonContentType::File)
                    <div class="lesson-block__media">
                        <div class="lesson-block__media-icon"><x-nav-icon name="file" class="size-6" /></div>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $data['name'] ?? 'مرفق' }}</p>
                            <p class="nageeb-caption mt-1">مرفق قابل للتحميل للطلاب المشتركين</p>
                    @if ($content->accessUrl())
                        <a href="{{ $content->accessUrl() }}" class="nageeb-btn nageeb-btn--outline nageeb-btn--sm mt-2">معاينة الملف</a>
                    @endif
                        </div>
                    </div>
                    @break

                @case(LessonContentType::Text)
                    <div class="nageeb-field">
                        <label class="nageeb-label" for="block-body-{{ $content->id }}">النص</label>
                        <textarea
                            id="block-body-{{ $content->id }}"
                            name="body"
                            rows="6"
                            class="nageeb-textarea"
                            placeholder="اكتب شرح الدرس هنا…"
                            @input="scheduleSave($event)"
                        >{{ $data['body'] ?? '' }}</textarea>
                    </div>
                    @break

                @case(LessonContentType::Link)
                    <div class="nageeb-field">
                        <label class="nageeb-label" for="block-url-{{ $content->id }}">الرابط</label>
                        <input
                            id="block-url-{{ $content->id }}"
                            type="url"
                            name="url"
                            dir="ltr"
                            class="nageeb-input"
                            value="{{ $data['url'] ?? '' }}"
                            placeholder="https://"
                            @input="scheduleSave($event)"
                        >
                    </div>
                    @break

                @case(LessonContentType::LiveSession)
                    <div class="lesson-block__grid">
                        <div class="nageeb-field">
                            <label class="nageeb-label" for="block-schedule-{{ $content->id }}">موعد الحصة</label>
                            <input
                                id="block-schedule-{{ $content->id }}"
                                type="datetime-local"
                                name="scheduled_at"
                                class="nageeb-input"
                                value="{{ $data['scheduled_at'] ?? '' }}"
                                @change="scheduleSave($event)"
                            >
                        </div>
                        <div class="nageeb-field">
                            <label class="nageeb-label" for="block-url-{{ $content->id }}">رابط الحصة</label>
                            <input
                                id="block-url-{{ $content->id }}"
                                type="url"
                                name="url"
                                dir="ltr"
                                class="nageeb-input"
                                value="{{ $data['url'] ?? '' }}"
                                placeholder="https://"
                                @input="scheduleSave($event)"
                            >
                        </div>
                    </div>
                    @break

                @default
                    <div class="nageeb-field">
                        <label class="nageeb-label" for="block-instructions-{{ $content->id }}">التعليمات</label>
                        <textarea
                            id="block-instructions-{{ $content->id }}"
                            name="instructions"
                            rows="4"
                            class="nageeb-textarea"
                            placeholder="اشرح للطالب المطلوب من {{ $type->label() }}…"
                            @input="scheduleSave($event)"
                        >{{ $data['instructions'] ?? '' }}</textarea>
                    </div>
            @endswitch
        </div>

        <div class="lesson-block__side">
            <div class="nageeb-field">
                <label class="nageeb-label" for="block-status-{{ $content->id }}">الحالة</label>
                <select id="block-status-{{ $content->id }}" name="status" class="nageeb-select" @change="scheduleSave($event)">
                    <option value="{{ ContentStatus::Draft->value }}" @selected($content->status === ContentStatus::Draft)>مسودة</option>
                    <option value="{{ ContentStatus::Live->value }}" @selected($content->status === ContentStatus::Live)>منشور</option>
                </select>
            </div>

            <fieldset class="nageeb-field">
                <legend class="nageeb-label">إتاحة المحتوى</legend>
                <label class="lesson-block__choice">
                    <input type="radio" name="region_scope" value="all" x-model="scope" @change="scheduleSave($event)">
                    <span>جميع المناطق</span>
                </label>
                <label class="lesson-block__choice">
                    <input type="radio" name="region_scope" value="selected" x-model="scope" @change="scheduleSave($event)">
                    <span>مناطق محددة</span>
                </label>

                <div class="lesson-block__regions-list" x-show="scope === 'selected'" x-cloak x-transition.opacity>
                    @forelse ($regions as $region)
                        <label class="lesson-block__choice">
                            <input
                                type="checkbox"
                                name="region_ids[]"
                                value="{{ $region->id }}"
                                class="nageeb-checkbox"
                                @checked($content->regions->contains($region->id))
                                @change="scheduleSave($event)"
                            >
                            <span>{{ $region->name }}</span>
                        </label>
                    @empty
                        <p class="nageeb-caption">لا توجد مناطق مفعّلة حاليًا.</p>
                    @endforelse
                </div>
            </fieldset>
        </div>
    </div>
</article>
