<?php

namespace App\Models;

use App\Enums\AttemptStatus;
use App\Enums\ExamDeliveryMode;
use App\Enums\ExamStatus;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'teacher_id',
    'course_id',
    'semester_id',
    'unit_id',
    'lesson_id',
    'title',
    'description',
    'duration_minutes',
    'max_attempts',
    'passing_score',
    'status',
    'show_results_immediately',
    'show_correct_answers',
    'shuffle_questions',
    'shuffle_options',
    'delivery_mode',
    'file_disk',
    'file_path',
    'file_original_name',
    'file_mime',
    'file_size',
    'file_uploaded_at',
])]
class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ExamStatus::class,
            'passing_score' => 'decimal:2',
            'show_results_immediately' => 'boolean',
            'show_correct_answers' => 'boolean',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'delivery_mode' => ExamDeliveryMode::class,
            'file_uploaded_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot(['points', 'position'])
            ->withTimestamps()
            ->orderBy('exam_questions.position');
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'exam_region')->withTimestamps();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ExamStatus::Published);
    }

    public function isOwnedBy(int $teacherId): bool
    {
        return $this->teacher_id === $teacherId;
    }

    public function isFileExam(): bool
    {
        return $this->delivery_mode === ExamDeliveryMode::File;
    }

    public function hasPaperFile(): bool
    {
        return $this->isFileExam() && filled($this->file_path);
    }

    public function paperUrl(): ?string
    {
        return $this->hasPaperFile()
            ? route('media.exams.show', $this)
            : null;
    }

    public function isPublished(): bool
    {
        return $this->status === ExamStatus::Published;
    }

    public function isAvailableToAllRegions(): bool
    {
        return $this->regions()->doesntExist();
    }

    /**
     * The exam grade is derived from its questions so the two can never drift apart.
     */
    public function totalPoints(): float
    {
        $this->loadMissing('questions');

        return (float) $this->questions->sum(
            fn (Question $question) => (float) ($question->pivot->points ?? $question->points),
        );
    }

    public function pointsFor(Question $question): float
    {
        $this->loadMissing('questions');

        $attached = $this->questions->firstWhere('id', $question->id);

        return (float) ($attached?->pivot?->points ?? $question->points);
    }

    public function nextQuestionPosition(): int
    {
        return (int) $this->questions()->max('exam_questions.position') + 1;
    }

    public function attemptsUsedBy(User $student): int
    {
        return $this->attempts()->where('student_id', $student->id)->count();
    }

    public function studentHasAttemptsLeft(User $student): bool
    {
        return $this->attemptsUsedBy($student) < max(1, (int) $this->max_attempts);
    }

    public function openAttemptFor(User $student): ?ExamAttempt
    {
        return $this->attempts()
            ->where('student_id', $student->id)
            ->where('status', AttemptStatus::InProgress)
            ->latest('id')
            ->first();
    }

    /**
     * Where the exam sits in the Course → Semester → Unit → Lesson hierarchy.
     */
    public function placementLabel(): string
    {
        $this->loadMissing(['course', 'semester', 'unit', 'lesson']);

        return collect([
            $this->course?->title,
            $this->semester?->title,
            $this->unit?->title,
            $this->lesson?->title,
        ])->filter()->join(' › ');
    }
}
