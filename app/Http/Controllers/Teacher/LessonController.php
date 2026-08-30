<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\SaveLessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Region;
use App\Models\Unit;
use App\Support\PositionSwap;
use App\Support\VideoAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function create(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load(['semesters.units']);

        $units = $course->semesters->flatMap->units->values();

        if ($units->isEmpty()) {
            return view('teacher.lessons.create', [
                'course' => $course,
                'lesson' => null,
                'needsUnit' => true,
            ]);
        }

        $requestedUnitId = old('unit_id', request()->query('unit'));
        $selectedUnitId = $units->firstWhere('id', (int) $requestedUnitId)?->id
            ?? $units->first()->id;

        return view('teacher.lessons.create', [
            'course' => $course,
            'lesson' => null,
            'needsUnit' => false,
            'selectedUnitId' => $selectedUnitId,
        ]);
    }

    public function store(SaveLessonRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        abort_if($course->units()->doesntExist(), 422);

        $unit = Unit::query()->findOrFail($request->integer('unit_id'));
        abort_unless($unit->belongsToCourse($course), 422);

        $lesson = $unit->lessons()->create([
            ...$request->lessonAttributes(),
            'unit_id' => $unit->id,
            'position' => $unit->nextLessonPosition(),
        ]);

        $this->syncContentRegions($this->storeMedia($lesson, $request), $request);

        return redirect()
            ->route('teacher.courses.lessons.edit', [$course, $lesson])
            ->with('status', 'تم إنشاء الدرس. أضف محتوى الدرس الآن.');
    }

    public function edit(Course $course, Lesson $lesson): View
    {
        $this->authorize('update', $course);
        abort_unless($lesson->belongsToCourse($course), 404);

        $course->load(['semesters.units']);
        $lesson->load(['contents.regions', 'unit.semester']);

        return view('teacher.lessons.edit', [
            'course' => $course,
            'lesson' => $lesson,
            'needsUnit' => false,
            'selectedUnitId' => old('unit_id', $lesson->unit_id),
            'regions' => Region::query()->active()->get(),
        ]);
    }

    public function update(SaveLessonRequest $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($lesson->belongsToCourse($course), 404);

        $oldUnit = $lesson->unit;
        $attributes = $request->lessonAttributes();
        $newUnitId = (int) $attributes['unit_id'];

        if ($newUnitId !== $oldUnit->id) {
            $newUnit = Unit::query()->findOrFail($newUnitId);
            abort_unless($newUnit->belongsToCourse($course), 422);
            $attributes['position'] = $newUnit->nextLessonPosition();
        }

        $lesson->update($attributes);

        if ($newUnitId !== $oldUnit->id) {
            $oldUnit->resequenceLessons();
        }

        $this->syncContentRegions($this->storeMedia($lesson->fresh(), $request), $request);

        return redirect()
            ->route('teacher.courses.lessons.edit', [$course, $lesson])
            ->with('status', 'تم حفظ تعديلات الدرس.');
    }

    public function destroy(Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeLesson($course, $lesson);

        $unit = $lesson->unit;
        $lesson->delete();
        $unit->resequenceLessons();

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تم حذف الدرس.');
    }

    public function duplicate(Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeLesson($course, $lesson);

        $lesson->load('contents.regions');

        $copy = $lesson->unit->lessons()->create([
            'title' => 'نسخة من '.$lesson->title,
            'description' => $lesson->description,
            'status' => ContentStatus::Draft,
            'is_preview' => $lesson->is_preview,
            'estimated_duration' => $lesson->estimated_duration,
            'position' => $lesson->unit->nextLessonPosition(),
        ]);

        foreach ($lesson->contents as $content) {
            $copyContent = $copy->contents()->create([
                'type' => $content->type,
                'title' => $content->title,
                'position' => $content->position,
                'data' => $content->data,
                'status' => $content->status,
            ]);
            $copyContent->regions()->sync($content->regions->pluck('id'));
        }

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تم نسخ الدرس.');
    }

    public function move(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeLesson($course, $lesson);

        $direction = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ])['direction'];

        $unit = $lesson->unit;
        $swap = $direction === 'up'
            ? $unit->lessons()->where('position', '<', $lesson->position)->orderByDesc('position')->first()
            : $unit->lessons()->where('position', '>', $lesson->position)->orderBy('position')->first();

        PositionSwap::adjacent(
            $lesson,
            $swap,
            fn () => (int) $unit->lessons()->max('position'),
        );

        return redirect()->route('teacher.courses.content', $course);
    }

    public function relocate(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $this->authorizeLesson($course, $lesson);

        $data = $request->validate([
            'unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->whereIn(
                    'semester_id',
                    $course->semesters()->select('id'),
                ),
            ],
        ]);

        $target = Unit::query()->findOrFail($data['unit_id']);
        $source = $lesson->unit;

        if ($target->id !== $source->id) {
            $lesson->update([
                'unit_id' => $target->id,
                'position' => $target->nextLessonPosition(),
            ]);
            $source->resequenceLessons();
        }

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تم نقل الدرس.');
    }

    public function reorder(Request $request, Course $course, Unit $unit): JsonResponse
    {
        $this->authorize('update', $course);
        abort_unless($unit->belongsToCourse($course), 404);

        $ids = collect($request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ])['ids'])->map(fn ($id) => (int) $id);

        abort_unless(
            $ids->sort()->values()->all() === $unit->lessons()->pluck('id')->sort()->values()->all(),
            422,
        );

        DB::transaction(function () use ($ids, $unit): void {
            $offset = (int) $unit->lessons()->max('position') + $ids->count() + 1;
            $ids->each(fn (int $id, int $index) => Lesson::query()->whereKey($id)->update(['position' => $offset + $index]));
            $ids->each(fn (int $id, int $index) => Lesson::query()->whereKey($id)->update(['position' => $index + 1]));
        });

        return response()->json(['saved' => true]);
    }

    public function reorderContents(Request $request, Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorizeLesson($course, $lesson);

        $ids = collect($request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ])['ids'])->map(fn ($id) => (int) $id);

        abort_unless(
            $ids->sort()->values()->all() === $lesson->contents()->pluck('id')->sort()->values()->all(),
            422,
        );

        DB::transaction(function () use ($ids, $lesson): void {
            $offset = (int) $lesson->contents()->max('position') + $ids->count() + 1;
            $ids->each(fn (int $id, int $index) => $lesson->contents()->whereKey($id)->update(['position' => $offset + $index]));
            $ids->each(fn (int $id, int $index) => $lesson->contents()->whereKey($id)->update(['position' => $index + 1]));
        });

        return response()->json(['saved' => true]);
    }

    /**
     * @return Collection<int, \App\Models\LessonContent>
     */
    private function storeMedia(Lesson $lesson, SaveLessonRequest $request): Collection
    {
        $position = $lesson->nextContentPosition();
        $created = collect();

        foreach ((array) $request->file('videos', []) as $file) {
            if ($file instanceof UploadedFile) {
                $created->push(VideoAsset::store($lesson, $file, $position));
                $position++;
            }
        }

        foreach ((array) $request->file('attachments', []) as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $created->push(VideoAsset::storeFile($lesson, $file, $position, LessonContentType::File));
            $position++;
        }

        return $created;
    }

    /**
     * Regional availability is managed per content block in the lesson builder,
     * so the lesson form only seeds the blocks it just uploaded.
     *
     * @param  Collection<int, \App\Models\LessonContent>  $contents
     */
    private function syncContentRegions(Collection $contents, SaveLessonRequest $request): void
    {
        $scope = $request->validated('region_scope');
        $regionIds = $scope === 'selected' ? ($request->validated('region_ids') ?? []) : [];

        foreach ($contents as $content) {
            $content->regions()->sync($regionIds);
        }
    }

    private function authorizeLesson(Course $course, Lesson $lesson): void
    {
        $this->authorize('update', $course);
        abort_unless($lesson->belongsToCourse($course), 404);
    }
}
