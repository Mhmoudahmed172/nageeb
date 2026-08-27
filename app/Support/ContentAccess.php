<?php

namespace App\Support;

use App\Enums\ContentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Support\Collection;

class ContentAccess
{
    public function canAccessLessonContent(?User $user, LessonContent $content): bool
    {
        if (! $user?->isStudent()) {
            return false;
        }

        return $this->studentCanAccessContent($user, $content);
    }

    public function studentCanAccessCourse(User $user, Course $course): bool
    {
        if (! $user->isStudent()) {
            return false;
        }

        if ($course->isFreeForStudents()) {
            return true;
        }

        return $this->activeEnrollmentsFor($user, $course)->isNotEmpty();
    }

    public function studentCanAccessSemester(User $user, Semester $semester): bool
    {
        $course = $semester->course;

        if ($course->isFreeForStudents() && $user->isStudent()) {
            return true;
        }

        foreach ($this->activeEnrollmentsFor($user, $course) as $enrollment) {
            if ($this->enrollmentUnlocksSemester($enrollment, $semester->id)) {
                return true;
            }
        }

        return false;
    }

    public function studentCanAccessLesson(User $user, Lesson $lesson): bool
    {
        $lesson->loadMissing('unit.semester.course');

        if ($lesson->is_preview && $user->isStudent()) {
            return true;
        }

        return $this->studentCanAccessSemester($user, $lesson->unit->semester);
    }

    public function studentCanAccessContent(User $user, LessonContent $content): bool
    {
        if (! $user->isStudent()) {
            return false;
        }

        $content->loadMissing(['lesson.unit.semester.course', 'regions']);

        if ($content->status !== ContentStatus::Live) {
            return false;
        }

        if (! $this->studentCanAccessLesson($user, $content->lesson)) {
            return false;
        }

        return $this->regionAllows($user, $content);
    }

    public function regionAllows(User $user, LessonContent $content): bool
    {
        $content->loadMissing('regions');

        if ($content->regions->isEmpty()) {
            return true;
        }

        $regionId = $user->studentProfile?->region_id;

        if (! $regionId) {
            return false;
        }

        return $content->regions->contains('id', $regionId);
    }

    /**
     * @return Collection<int, Enrollment>
     */
    public function activeEnrollmentsFor(User $user, Course $course): Collection
    {
        return Enrollment::query()
            ->with(['accessPlan.semesters'])
            ->where('student_id', $user->id)
            ->where('course_id', $course->id)
            ->where(function ($query) {
                $query->where('status', 'active')->orWhereNull('status');
            })
            ->active()
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->isActive())
            ->values();
    }

    public function enrollmentUnlocksSemester(Enrollment $enrollment, int $semesterId): bool
    {
        if (! $enrollment->isActive()) {
            return false;
        }

        $plan = $enrollment->accessPlan;

        if (! $plan) {
            return true;
        }

        $plan->loadMissing('semesters');

        return $plan->unlocksSemester($semesterId);
    }
}
