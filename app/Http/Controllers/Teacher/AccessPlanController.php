<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\SaveAccessPlanRequest;
use App\Models\AccessPlan;
use App\Models\Course;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccessPlanController extends Controller
{
    public function index(Course $course): View
    {
        $this->authorize('update', $course);

        $course->load('semesters.units');
        $plans = AccessPlan::query()
            ->where('course_id', $course->id)
            ->with(['prices.region', 'semesters'])
            ->withCount('enrollments')
            ->latest()
            ->get();
        $regions = Region::query()->active()->get();

        return view('teacher.packages.index', [
            'course' => $course,
            'plans' => $plans,
            'regions' => $regions,
        ]);
    }

    public function store(SaveAccessPlanRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $this->persist($course, new AccessPlan, $request);

        return redirect()
            ->route('teacher.courses.packages.index', $course)
            ->with('status', 'تمت إضافة خطة الوصول.');
    }

    public function update(SaveAccessPlanRequest $request, Course $course, AccessPlan $accessPlan): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($accessPlan->course_id === $course->id, 404);

        $this->persist($course, $accessPlan, $request);

        return redirect()
            ->route('teacher.courses.packages.index', $course)
            ->with('status', 'تم تحديث خطة الوصول.');
    }

    public function destroy(Course $course, AccessPlan $accessPlan): RedirectResponse
    {
        $this->authorize('update', $course);
        abort_unless($accessPlan->course_id === $course->id, 404);
        abort_if($accessPlan->enrollments()->exists(), 422);

        $accessPlan->delete();

        return redirect()
            ->route('teacher.courses.packages.index', $course)
            ->with('status', 'تم حذف خطة الوصول.');
    }

    private function persist(Course $course, AccessPlan $plan, SaveAccessPlanRequest $request): void
    {
        DB::transaction(function () use ($course, $plan, $request): void {
            $plan->fill([
                'course_id' => $course->id,
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'status' => $request->validated('status'),
                'access_duration_days' => $request->validated('access_duration_days'),
            ]);
            $plan->save();

            $plan->semesters()->sync($request->validated('semester_ids'));

            $keptRegionIds = [];
            foreach ($request->validated('prices') as $price) {
                $keptRegionIds[] = $price['region_id'];
                $plan->prices()->updateOrCreate(
                    [
                        'access_plan_id' => $plan->id,
                        'region_id' => $price['region_id'],
                    ],
                    [
                        'price' => $price['price'],
                        'sale_price' => $price['sale_price'] ?? null,
                        'currency' => $price['currency'] ?? 'ILS',
                    ],
                );
            }

            $plan->prices()->whereNotIn('region_id', $keptRegionIds)->delete();
        });
    }
}
