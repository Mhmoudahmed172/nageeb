<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

#[Fillable(['semester_id', 'title', 'description', 'position', 'status'])]
class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
        ];
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function course(): HasOneThrough
    {
        return $this->hasOneThrough(
            Course::class,
            Semester::class,
            'id',
            'id',
            'semester_id',
            'course_id',
        );
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function belongsToCourse(Course $course): bool
    {
        return $this->semester?->course_id === $course->id;
    }

    public function nextLessonPosition(): int
    {
        return (int) $this->lessons()->max('position') + 1;
    }

    public function lessonsCountLabel(): string
    {
        $count = $this->lessons->count();

        return match (true) {
            $count === 0 => 'لا دروس',
            $count === 1 => 'درس واحد',
            $count === 2 => 'درسان',
            $count <= 10 => $count.' دروس',
            default => $count.' درساً',
        };
    }

    public function resequenceLessons(): void
    {
        $lessons = $this->lessons()->orderBy('position')->orderBy('id')->get();
        $offset = $lessons->count() + 1000;

        foreach ($lessons as $index => $lesson) {
            $lesson->update(['position' => $offset + $index]);
        }

        foreach ($lessons as $index => $lesson) {
            $lesson->update(['position' => $index + 1]);
        }
    }
}
