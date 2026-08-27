<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\SubscriptionRequest;
use App\Models\User;
use App\Notifications\StudentInactivityReminderNotification;
use App\Support\TeacherDashboardMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user()->load('teacherProfile');
        $metrics = (new TeacherDashboardMetrics($user->id))->toArray();
        $courses = Course::query()
            ->forTeacher($user->id)
            ->withCount([
                'units',
                'accessPlans',
                'enrollments as active_enrollments_count' => fn ($query) => $query->active(),
            ])
            ->latest('updated_at')
            ->get();
        $courseIds = $courses->pluck('id');

        $coursePerformance = $courses->map(function (Course $course) {
            $activeEnrollments = Enrollment::query()
                ->active()
                ->where('course_id', $course->id)
                ->get(['student_id', 'amount_paid']);
            $lessonIds = Lesson::query()
                ->whereHas('unit.semester', fn ($query) => $query->where('course_id', $course->id))
                ->pluck('id');
            $lessonCount = $lessonIds->count();
            $studentsCount = $activeEnrollments->pluck('student_id')->unique()->count();
            $viewedPairs = $lessonCount === 0 || $studentsCount === 0
                ? 0
                : \App\Models\LessonView::query()
                    ->whereIn('lesson_id', $lessonIds)
                    ->whereIn('student_id', $activeEnrollments->pluck('student_id'))
                    ->select(['student_id', 'lesson_id'])
                    ->distinct()
                    ->count();

            return [
                'course' => $course,
                'students' => $studentsCount,
                'active_subscriptions' => $activeEnrollments->count(),
                'completion' => $lessonCount && $studentsCount
                    ? (int) round(($viewedPairs / ($lessonCount * $studentsCount)) * 100)
                    : 0,
                'revenue' => (float) $activeEnrollments->sum('amount_paid'),
            ];
        });

        $recentActivity = collect()
            ->merge(
                SubscriptionRequest::query()
                    ->with(['student', 'course'])
                    ->whereIn('course_id', $courseIds)
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn (SubscriptionRequest $request) => [
                        'type' => 'request',
                        'title' => 'طلب اشتراك جديد',
                        'description' => $request->student->name.' · '.$request->course->title,
                        'at' => $request->created_at,
                        'url' => route('teacher.subscription-requests.index'),
                    ]),
            )
            ->merge(
                Enrollment::query()
                    ->with(['student', 'course'])
                    ->whereIn('course_id', $courseIds)
                    ->latest('granted_at')
                    ->take(5)
                    ->get()
                    ->map(fn (Enrollment $enrollment) => [
                        'type' => 'student',
                        'title' => 'انضم طالب جديد',
                        'description' => $enrollment->student->name.' · '.$enrollment->course->title,
                        'at' => $enrollment->granted_at ?? $enrollment->created_at,
                        'url' => route('teacher.enrollments.index'),
                    ]),
            )
            ->merge(
                Lesson::query()
                    ->with('unit.semester.course')
                    ->whereHas('unit.semester', fn ($query) => $query->whereIn('course_id', $courseIds))
                    ->latest('updated_at')
                    ->take(5)
                    ->get()
                    ->map(fn (Lesson $lesson) => [
                        'type' => 'lesson',
                        'title' => 'تم تحديث درس',
                        'description' => $lesson->title.' · '.$lesson->unit->semester->course->title,
                        'at' => $lesson->updated_at,
                        'url' => route('teacher.courses.content', $lesson->unit->semester->course),
                    ]),
            )
            ->sortByDesc('at')
            ->take(7)
            ->values();

        return view('dashboards.teacher', [
            'user' => $user,
            'profile' => $user->teacherProfile,
            'courses' => $courses,
            'dashboardCoursePerformance' => $coursePerformance,
            'recentActivity' => $recentActivity,
            'draftCoursesCount' => $courses->where('status', \App\Enums\CourseStatus::Draft)->count(),
            'incompleteCoursesCount' => $courses->filter(
                fn (Course $course) => $course->units_count === 0 || $course->access_plans_count === 0,
            )->count(),
            'lessonsWithoutContentCount' => Lesson::query()
                ->whereHas('unit.semester', fn ($query) => $query->whereIn('course_id', $courseIds))
                ->whereDoesntHave('contents')
                ->count(),
            'chartPayload' => [
                'growth' => $metrics['studentsGrowthByMonth'],
                'region' => $metrics['regionDistribution'],
                'courses' => $metrics['coursePerformance'],
            ],
            ...$metrics,
        ]);
    }

    public function remind(Request $request, User $student): RedirectResponse
    {
        $course = Course::query()->findOrFail($request->integer('course_id'));

        abort_unless($course->teacher_id === auth()->id(), 403);
        abort_unless($student->isStudent(), 404);
        abort_unless(
            Enrollment::query()
                ->where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->active()
                ->exists(),
            403,
        );

        $student->notify(new StudentInactivityReminderNotification($course, auth()->user()));

        return redirect()
            ->route('teacher.dashboard')
            ->with('status', 'تم إرسال تذكير إلى '.$student->name.'.');
    }
}
