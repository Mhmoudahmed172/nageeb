<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\SaveSemesterRequest;
use App\Models\Course;
use App\Models\Semester;
use App\Support\PositionSwap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function create(Course $course): View
    {
        $this->authorize('update', $course);

        return view('teacher.semesters.create', compact('course'));
    }

    public function store(SaveSemesterRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->semesters()->create([
            ...$request->validated(),
            'position' => $course->nextSemesterPosition(),
        ]);

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تمت إضافة الفصل.');
    }

    public function edit(Course $course, Semester $semester): View
    {
        $this->authorize('update', $course);
        abort_unless($semester->course_id === $course->id, 404);

        return view('teacher.semesters.edit', compact('course', 'semester'));
    }

    public function update(SaveSemesterRequest $request, Course $course, Semester $semester): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($semester->course_id === $course->id, 404);

        $semester->update($request->validated());

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تم حفظ تعديلات الفصل.');
    }

    public function destroy(Course $course, Semester $semester): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($semester->course_id === $course->id, 404);
        abort_if($course->semesters()->count() <= 1, 422);

        $semester->delete();

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تم حذف الفصل.');
    }

    public function move(Request $request, Course $course, Semester $semester): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($semester->course_id === $course->id, 404);

        $direction = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ])['direction'];

        $swap = $direction === 'up'
            ? $course->semesters()->where('position', '<', $semester->position)->orderByDesc('position')->first()
            : $course->semesters()->where('position', '>', $semester->position)->orderBy('position')->first();

        PositionSwap::adjacent(
            $semester,
            $swap,
            fn () => (int) $course->semesters()->max('position'),
        );

        return redirect()->route('teacher.courses.content', $course);
    }

    public function reorder(Request $request, Course $course): JsonResponse
    {
        $this->authorize('update', $course);

        $ids = collect($request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ])['ids'])->map(fn ($id) => (int) $id);

        abort_unless(
            $ids->sort()->values()->all() === $course->semesters()->pluck('id')->sort()->values()->all(),
            422,
        );

        DB::transaction(function () use ($ids, $course): void {
            $offset = (int) $course->semesters()->max('position') + $ids->count() + 1;
            $ids->each(fn (int $id, int $index) => Semester::query()->whereKey($id)->update(['position' => $offset + $index]));
            $ids->each(fn (int $id, int $index) => Semester::query()->whereKey($id)->update(['position' => $index + 1]));
        });

        return response()->json(['saved' => true]);
    }
}
