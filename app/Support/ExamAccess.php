<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Student-side authorization for exams. Placement decides which access rule
 * applies, so an exam is never easier to reach than the content it belongs to.
 */
class ExamAccess
{
    public function __construct(private readonly ContentAccess $contentAccess) {}

    public function studentCanAccessExam(?User $user, Exam $exam): bool
    {
        if (! $user?->isStudent()) {
            return false;
        }

        if (! $exam->isPublished()) {
            return false;
        }

        return $this->placementAllows($user, $exam) && $this->regionAllows($user, $exam);
    }

    public function regionAllows(User $user, Exam $exam): bool
    {
        $exam->loadMissing('regions');

        if ($exam->regions->isEmpty()) {
            return true;
        }

        $regionId = $user->studentProfile?->region_id;

        if (! $regionId) {
            return false;
        }

        return $exam->regions->contains('id', $regionId);
    }

    /**
     * @return Collection<int, Exam>
     */
    public function publishedExamsForCourse(User $user, Course $course): Collection
    {
        return Exam::query()
            ->published()
            ->where('course_id', $course->id)
            ->with(['regions', 'lesson.unit.semester', 'unit.semester', 'semester', 'course'])
            ->withCount('questions')
            ->orderBy('title')
            ->get()
            ->filter(fn (Exam $exam) => $this->studentCanAccessExam($user, $exam))
            ->values();
    }

    /**
     * @return Collection<int, Exam>
     */
    public function publishedExamsForLesson(User $user, Lesson $lesson): Collection
    {
        return Exam::query()
            ->published()
            ->where('lesson_id', $lesson->id)
            ->with(['regions', 'lesson.unit.semester', 'course'])
            ->withCount('questions')
            ->orderBy('title')
            ->get()
            ->filter(fn (Exam $exam) => $this->studentCanAccessExam($user, $exam))
            ->values();
    }

    private function placementAllows(User $user, Exam $exam): bool
    {
        $exam->loadMissing(['course', 'semester', 'unit.semester', 'lesson.unit.semester']);

        if ($exam->lesson) {
            return $this->contentAccess->studentCanAccessLesson($user, $exam->lesson);
        }

        if ($exam->unit?->semester) {
            return $this->contentAccess->studentCanAccessSemester($user, $exam->unit->semester);
        }

        if ($exam->semester) {
            return $this->contentAccess->studentCanAccessSemester($user, $exam->semester);
        }

        return $this->contentAccess->studentCanAccessCourse($user, $exam->course);
    }
}
