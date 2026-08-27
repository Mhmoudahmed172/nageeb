<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Support\VideoAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LessonContentController extends Controller
{
    public function store(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeLesson($course, $lesson);

        $type = LessonContentType::tryFrom((string) $request->input('type'));
        abort_if($type === null, 422);

        $request->validate([
            'type' => ['required', Rule::enum(LessonContentType::class)],
            'file' => match ($type) {
                LessonContentType::Video => ['required', 'file', 'mimes:mp4,webm,mov,avi', 'max:102400'],
                LessonContentType::Audio => ['required', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:51200'],
                LessonContentType::File => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,zip,doc,docx', 'max:10240'],
                default => ['prohibited'],
            },
        ]);

        $file = $request->file('file');
        $position = $lesson->nextContentPosition();

        if ($type === LessonContentType::Video && $file) {
            VideoAsset::store($lesson, $file, $position);
        } elseif ($file) {
            $lesson->contents()->create([
                'type' => $type,
                'title' => $file->getClientOriginalName(),
                'position' => $position,
                'status' => ContentStatus::Draft,
                'data' => [
                    'name' => $file->getClientOriginalName(),
                    'path' => $file->store($this->diskFolder($type), 'public'),
                    'state' => VideoAsset::STATE_READY,
                ],
            ]);
        } else {
            $lesson->contents()->create([
                'type' => $type,
                'title' => null,
                'position' => $position,
                'status' => ContentStatus::Draft,
                'data' => [],
            ]);
        }

        return back()->with('status', 'تمت إضافة «'.$type->label().'» إلى الدرس.');
    }

    public function update(Request $request, Course $course, Lesson $lesson, LessonContent $content): JsonResponse|RedirectResponse
    {
        $this->authorizeLesson($course, $lesson);
        abort_unless($content->lesson_id === $lesson->id, 404);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([ContentStatus::Draft->value, ContentStatus::Live->value])],
            'region_scope' => ['required', Rule::in(['all', 'selected'])],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['integer', Rule::exists('regions', 'id')],
            'body' => ['nullable', 'string', 'max:50000'],
            'url' => ['nullable', 'url', 'max:2048'],
            'instructions' => ['nullable', 'string', 'max:20000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $payload = collect($validated)
            ->only(['body', 'url', 'instructions', 'scheduled_at'])
            ->reject(fn ($value) => $value === null)
            ->all();

        $content->fill([
            'title' => $validated['title'] ?? null,
            'status' => $validated['status'],
            'data' => [...$content->data ?? [], ...$payload],
        ])->save();

        $content->regions()->sync(
            $validated['region_scope'] === 'selected' ? ($validated['region_ids'] ?? []) : [],
        );

        return $request->expectsJson()
            ? response()->json(['saved' => true])
            : back()->with('status', 'تم حفظ المحتوى.');
    }

    public function destroy(Course $course, Lesson $lesson, LessonContent $content): RedirectResponse
    {
        $this->authorizeLesson($course, $lesson);
        abort_unless($content->lesson_id === $lesson->id, 404);

        $content->delete();

        return back()->with('status', 'تم حذف المحتوى.');
    }

    private function diskFolder(LessonContentType $type): string
    {
        return $type === LessonContentType::Audio ? 'lessons/audio' : 'lessons/files';
    }

    private function authorizeLesson(Course $course, Lesson $lesson): void
    {
        $this->authorize('update', $course);
        abort_unless($lesson->belongsToCourse($course), 404);
    }
}
