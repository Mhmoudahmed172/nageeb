<?php

namespace App\Support;

use App\Enums\CourseStatus;
use App\Enums\SubscriptionRequestStatus;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonView;
use App\Models\SubscriptionRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeacherDashboardMetrics
{
    public function __construct(private readonly int $teacherId) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $teacherCourses = Course::query()->forTeacher($this->teacherId)->get();
        $courseIds = $teacherCourses->pluck('id');

        $activeEnrollments = Enrollment::query()
            ->with(['student.studentProfile', 'course'])
            ->active()
            ->whereIn('course_id', $courseIds)
            ->get();

        $activeStudentsCount = $activeEnrollments->pluck('student_id')->unique()->count();
        $previousActiveStudents = $this->activeStudentsAsOf(now()->subMonth()->endOfMonth(), $courseIds);

        $currentMonthEarnings = EnrollmentRevenue::total(
            $this->teacherId,
            now()->startOfMonth(),
            now()->endOfMonth(),
        );
        $previousMonthEarnings = EnrollmentRevenue::total(
            $this->teacherId,
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth(),
        );

        $pendingQuery = SubscriptionRequest::query()
            ->where('status', SubscriptionRequestStatus::Pending)
            ->whereIn('course_id', $courseIds);

        $pendingRequestsCount = (clone $pendingQuery)->count();
        $pendingAtMonthStart = (clone $pendingQuery)
            ->where('created_at', '<', now()->startOfMonth())
            ->count();

        $liveCoursesCount = $teacherCourses->where('status', CourseStatus::Live)->count();
        $liveCoursesLastMonth = $teacherCourses
            ->where('status', CourseStatus::Live)
            ->filter(fn (Course $course) => $course->created_at?->lte(now()->subMonth()->endOfMonth()))
            ->count();

        return [
            'activeStudentsCount' => $activeStudentsCount,
            'activeStudentsChange' => $this->change($activeStudentsCount, $previousActiveStudents),
            'currentMonthEarnings' => $currentMonthEarnings,
            'earningsChange' => $this->change($currentMonthEarnings, $previousMonthEarnings),
            'pendingRequestsCount' => $pendingRequestsCount,
            'pendingChange' => $this->change($pendingRequestsCount, $pendingAtMonthStart),
            'liveCoursesCount' => $liveCoursesCount,
            'liveCoursesChange' => $this->change($liveCoursesCount, $liveCoursesLastMonth),
            'studentsGrowthByMonth' => $this->studentsGrowthByMonth($courseIds),
            'regionDistribution' => $this->regionDistribution($activeEnrollments),
            'coursePerformance' => $this->coursePerformance($teacherCourses, $activeEnrollments),
            'atRiskStudents' => $this->atRiskStudents($activeEnrollments),
            'recentUnansweredQuestions' => $this->unansweredQuestions($courseIds),
            'weeklyPulse' => $this->weeklyPulse($courseIds),
            'growthCaption' => $this->caption($this->change($activeStudentsCount, $previousActiveStudents), 'عن الشهر الماضي في عدد الطلاب النشطين'),
            'regionCaption' => $this->regionCaption($activeEnrollments),
            'performanceCaption' => $this->performanceCaption($teacherCourses, $activeEnrollments),
        ];
    }

    /**
     * @param  Collection<int, int|string>  $courseIds
     */
    private function activeStudentsAsOf(Carbon $moment, Collection $courseIds): int
    {
        return Enrollment::query()
            ->whereIn('course_id', $courseIds)
            ->where('granted_at', '<=', $moment)
            ->where(function ($query) use ($moment) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', $moment);
            })
            ->pluck('student_id')
            ->unique()
            ->count();
    }

    /**
     * @param  Collection<int, int|string>  $courseIds
     * @return array{labels: list<string>, values: list<int>}
     */
    private function studentsGrowthByMonth(Collection $courseIds): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->locale('ar')->translatedFormat('M');
            $values[] = Enrollment::query()
                ->whereIn('course_id', $courseIds)
                ->whereBetween('granted_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->pluck('student_id')
                ->unique()
                ->count();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return array{labels: list<string>, values: list<int>}
     */
    private function regionDistribution(Collection $enrollments): array
    {
        $students = $enrollments->unique('student_id');

        $gaza = $students->filter(
            fn (Enrollment $enrollment) => $enrollment->student->studentProfile?->region?->code === 'gaza',
        )->count();

        $west = $students->count() - $gaza;

        return [
            'labels' => ['غزة', 'الضفة الغربية'],
            'values' => [$gaza, $west],
        ];
    }

    /**
     * @param  Collection<int, Course>  $courses
     * @param  Collection<int, Enrollment>  $enrollments
     * @return array{labels: list<string>, values: list<int>}
     */
    private function coursePerformance(Collection $courses, Collection $enrollments): array
    {
        $counts = $enrollments->groupBy('course_id')->map->count();

        $ranked = $courses
            ->map(fn (Course $course) => [
                'title' => $course->title,
                'students' => (int) $counts->get($course->id, 0),
            ])
            ->sortByDesc('students')
            ->values();

        return [
            'labels' => $ranked->pluck('title')->all(),
            'values' => $ranked->pluck('students')->all(),
        ];
    }

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     * @return list<array{student_id: int, student_name: string, course_id: int, course_title: string, inactive_days: int}>
     */
    private function atRiskStudents(Collection $enrollments): array
    {
        $cutoff = now()->subDays(10);
        $atRisk = [];

        foreach ($enrollments as $enrollment) {
            $lessonIds = Lesson::query()
                ->whereHas('unit.semester', fn ($query) => $query->where('course_id', $enrollment->course_id))
                ->pluck('id');

            $lastView = $lessonIds->isEmpty()
                ? null
                : LessonView::query()
                    ->where('student_id', $enrollment->student_id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->max('viewed_at');

            $lastQuestion = Comment::query()
                ->where('user_id', $enrollment->student_id)
                ->whereNull('parent_id')
                ->whereIn('lesson_id', $lessonIds)
                ->max('created_at');

            $lastActivity = collect([$lastView, $lastQuestion])
                ->filter()
                ->map(fn ($value) => Carbon::parse($value))
                ->sortDesc()
                ->first();

            if ($lastActivity && $lastActivity->gte($cutoff)) {
                continue;
            }

            $reference = $lastActivity ?? $enrollment->granted_at ?? now();
            $atRisk[] = [
                'student_id' => $enrollment->student_id,
                'student_name' => $enrollment->student->name,
                'course_id' => $enrollment->course_id,
                'course_title' => $enrollment->course->title,
                'inactive_days' => (int) $reference->diffInDays(now()),
            ];
        }

        usort($atRisk, fn ($a, $b) => $b['inactive_days'] <=> $a['inactive_days']);

        return array_slice($atRisk, 0, 5);
    }

    /**
     * @param  Collection<int, int|string>  $courseIds
     * @return Collection<int, Comment>
     */
    private function unansweredQuestions(Collection $courseIds): Collection
    {
        return Comment::query()
            ->with(['user', 'lesson.unit.course'])
            ->whereNull('parent_id')
            ->whereDoesntHave('replies')
            ->whereHas('lesson.unit.semester', fn ($query) => $query->whereIn('course_id', $courseIds))
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * @param  Collection<int, int|string>  $courseIds
     * @return list<array{date: string, label: string, level: string, score: int}>
     */
    private function weeklyPulse(Collection $courseIds): array
    {
        $lessonIds = Lesson::query()
            ->whereHas('unit.semester', fn ($query) => $query->whereIn('course_id', $courseIds))
            ->pluck('id');

        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $end = $day->copy()->endOfDay();

            $replies = Comment::query()
                ->whereNotNull('parent_id')
                ->where('user_id', $this->teacherId)
                ->whereIn('lesson_id', $lessonIds)
                ->whereBetween('created_at', [$day, $end])
                ->count();

            $lessonsAdded = Lesson::query()
                ->whereIn('id', $lessonIds)
                ->whereBetween('created_at', [$day, $end])
                ->count();

            $reviewed = SubscriptionRequest::query()
                ->whereIn('course_id', $courseIds)
                ->whereNotNull('reviewed_at')
                ->whereBetween('reviewed_at', [$day, $end])
                ->count();

            $score = $replies + $lessonsAdded + $reviewed;

            $days[] = [
                'date' => $day->toDateString(),
                'label' => $day->locale('ar')->translatedFormat('D'),
                'score' => $score,
                'level' => match (true) {
                    $score === 0 => 'none',
                    $score <= 2 => 'low',
                    $score <= 5 => 'medium',
                    default => 'high',
                },
            ];
        }

        return $days;
    }

    /**
     * @return array{direction: string, percent: int}
     */
    private function change(float|int $current, float|int $previous): array
    {
        if ((float) $previous === 0.0) {
            $percent = (float) $current > 0 ? 100 : 0;

            return [
                'direction' => $percent > 0 ? 'up' : 'flat',
                'percent' => $percent,
            ];
        }

        $percent = (int) round((($current - $previous) / $previous) * 100);

        return [
            'direction' => $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'flat'),
            'percent' => abs($percent),
        ];
    }

    /**
     * @param  array{direction: string, percent: int}  $change
     */
    private function caption(array $change, string $suffix): string
    {
        if ($change['direction'] === 'flat') {
            return 'لا تغيّر '.$suffix;
        }

        $word = $change['direction'] === 'up' ? 'زيادة' : 'انخفاض';

        return $word.' '.$change['percent'].'% '.$suffix;
    }

    /**
     * @param  Collection<int, Enrollment>  $enrollments
     */
    private function regionCaption(Collection $enrollments): string
    {
        $distribution = $this->regionDistribution($enrollments);
        $total = array_sum($distribution['values']);

        if ($total === 0) {
            return 'لا يوجد ملتحقون نشطون بعد لتقسيمهم جغرافياً.';
        }

        $gazaShare = (int) round(($distribution['values'][0] / $total) * 100);

        return 'غزة تمثّل '.$gazaShare.'% من الملتحقين النشطين.';
    }

    /**
     * @param  Collection<int, Course>  $courses
     * @param  Collection<int, Enrollment>  $enrollments
     */
    private function performanceCaption(Collection $courses, Collection $enrollments): string
    {
        $performance = $this->coursePerformance($courses, $enrollments);
        $top = $performance['labels'][0] ?? null;
        $topCount = $performance['values'][0] ?? 0;

        if (! $top || $topCount === 0) {
            return 'لم يُسجَّل ملتحقون على موادك بعد.';
        }

        return 'أعلى مادة من حيث الملتحقين: «'.$top.'» ('.$topCount.')';
    }
}
