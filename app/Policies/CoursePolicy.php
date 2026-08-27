<?php

namespace App\Policies;

use App\Models\AccessPlan;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonContent;
use App\Models\Semester;
use App\Models\Unit;
use App\Models\User;

class CoursePolicy
{
    public function view(User $user, Course $course): bool
    {
        return $this->owns($user, $course);
    }

    public function update(User $user, Course $course): bool
    {
        return $this->owns($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->owns($user, $course);
    }

    public function viewSemester(User $user, Course $course, Semester $semester): bool
    {
        return $this->owns($user, $course) && $semester->course_id === $course->id;
    }

    public function viewUnit(User $user, Course $course, Unit $unit): bool
    {
        return $this->owns($user, $course)
            && $unit->semester?->course_id === $course->id;
    }

    public function viewLesson(User $user, Course $course, Lesson $lesson): bool
    {
        return $this->owns($user, $course)
            && $lesson->unit?->semester?->course_id === $course->id;
    }

    public function viewAccessPlan(User $user, Course $course, AccessPlan $plan): bool
    {
        return $this->owns($user, $course) && $plan->course_id === $course->id;
    }

    public function viewEnrollment(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdmin()
            || ($user->isTeacher() && $enrollment->course?->teacher_id === $user->id);
    }

    public function viewLessonContent(User $user, Course $course, LessonContent $content): bool
    {
        return $this->viewLesson($user, $course, $content->lesson);
    }

    private function owns(User $user, Course $course): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && $course->isOwnedBy($user->id));
    }
}
