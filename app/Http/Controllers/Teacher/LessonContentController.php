<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreLessonContentRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Support\ExternalUrl;
use App\Support\MediaStore;
use App\Support\VideoAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LessonContentController extends Controller
{
    public function __construct(private readonly MediaStore $media) {}

    public function store(StoreLessonContentRequest $request, Course $course, Lesson $lesson): JsonResponse|RedirectResponse
    {
        $this->authorizeLesson($course, $lesson);

        $type = $request->contentType();
        $file = $request->file('file');
        $position = $lesson->nextContentPosition();

        try {
            if ($type === LessonContentType::Video && $file) {
                $content = VideoAsset::store($lesson, $file, $position);
            } elseif ($request->isMediaType() && $file) {
                $content = VideoAsset::storeFile($lesson, $file, $position, $type);
            } else {
                $data = [];

                if ($type === LessonContentType::Link && $request->filled('url')) {
                    $data['url'] = ExternalUrl::assert((string) $request->input('url'));
                    $data['source'] = 'external_link';
                }

                $content = $lesson->contents()->create([
                    'type' => $type,
                    'title' => null,
                    'position' => $position,
                    'status' => ContentStatus::Draft,
                    'data' => $data,
                ]);
            }
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            return back()->withErrors($exception->errors())->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'id' => $content->id,
                'type' => $type->value,
                'message' => 'تمت إضافة «'.$type->label().'» إلى الدرس.',
            ], 201);
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
            'url' => ['nullable', 'string', 'max:2048'],
            'instructions' => ['nullable', 'string', 'max:20000'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        if (filled($validated['url'] ?? null)) {
            $validated['url'] = ExternalUrl::assert((string) $validated['url']);
        }

        $payload = collect($validated)
            ->only(['body', 'url', 'instructions', 'scheduled_at'])
            ->reject(fn ($value) => $value === null)
            ->all();

        if (isset($payload['url'])) {
            $payload['source'] = 'external_link';
        }

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

        $this->media->delete($content->data['path'] ?? null, $content->data['disk'] ?? null);
        $content->delete();

        return back()->with('status', 'تم حذف المحتوى.');
    }

    private function authorizeLesson(Course $course, Lesson $lesson): void
    {
        $this->authorize('update', $course);
        abort_unless($lesson->belongsToCourse($course), 404);
    }
}
