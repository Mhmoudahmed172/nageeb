<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\SaveUnitRequest;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Unit;
use App\Support\PositionSwap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load(['semesters.units.lessons.contents']);

        return view('teacher.courses.content', compact('course'));
    }

    public function create(Course $course): View
    {
        $this->authorize('update', $course);
        $course->load('semesters');

        return view('teacher.units.create', [
            'course' => $course,
            'suggestedTitle' => $course->suggestedUnitTitle(),
        ]);
    }

    public function store(SaveUnitRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $semester = Semester::query()->findOrFail($request->integer('semester_id'));
        abort_unless($semester->course_id === $course->id, 404);

        $semester->units()->create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'status' => $request->validated('status'),
            'position' => $semester->nextUnitPosition(),
        ]);

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تمت إضافة الوحدة.');
    }

    public function edit(Course $course, Unit $unit): View
    {
        $this->authorize('update', $course);
        abort_unless($unit->belongsToCourse($course), 404);
        $course->load('semesters');

        return view('teacher.units.edit', compact('course', 'unit'));
    }

    public function update(SaveUnitRequest $request, Course $course, Unit $unit): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($unit->belongsToCourse($course), 404);

        $semester = Semester::query()->findOrFail($request->integer('semester_id'));
        abort_unless($semester->course_id === $course->id, 404);

        $unit->update([
            'semester_id' => $semester->id,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'status' => $request->validated('status'),
        ]);

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تم حفظ تعديلات الوحدة.');
    }

    public function destroy(Course $course, Unit $unit): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($unit->belongsToCourse($course), 404);

        $semester = $unit->semester;
        $unit->delete();
        $semester->resequenceUnits();

        return redirect()
            ->route('teacher.courses.content', $course)
            ->with('status', 'تم حذف الوحدة.');
    }

    public function move(Request $request, Course $course, Unit $unit): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($unit->belongsToCourse($course), 404);

        $direction = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ])['direction'];

        $semester = $unit->semester;
        $swap = $direction === 'up'
            ? $semester->units()->where('position', '<', $unit->position)->orderByDesc('position')->first()
            : $semester->units()->where('position', '>', $unit->position)->orderBy('position')->first();

        PositionSwap::adjacent(
            $unit,
            $swap,
            fn () => (int) $semester->units()->max('position'),
        );

        return redirect()->route('teacher.courses.content', $course);
    }

    public function reorder(Request $request, Course $course, Semester $semester): JsonResponse
    {
        $this->authorize('update', $course);
        abort_unless($semester->course_id === $course->id, 404);

        $ids = collect($request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ])['ids'])->map(fn ($id) => (int) $id);

        abort_unless(
            $ids->sort()->values()->all() === $semester->units()->pluck('id')->sort()->values()->all(),
            422,
        );

        DB::transaction(function () use ($ids, $semester): void {
            $offset = (int) $semester->units()->max('position') + $ids->count() + 1;
            $ids->each(fn (int $id, int $index) => Unit::query()->whereKey($id)->update(['position' => $offset + $index]));
            $ids->each(fn (int $id, int $index) => Unit::query()->whereKey($id)->update(['position' => $index + 1]));
        });

        return response()->json(['saved' => true]);
    }
}
