<?php

namespace App\Models;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable([
    'teacher_id',
    'course_id',
    'unit_id',
    'type',
    'text',
    'explanation',
    'points',
    'difficulty',
    'data',
])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'difficulty' => QuestionDifficulty::class,
            'points' => 'decimal:2',
            'data' => 'array',
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

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('position');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
            ->withPivot(['points', 'position'])
            ->withTimestamps();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function isOwnedBy(int $teacherId): bool
    {
        return $this->teacher_id === $teacherId;
    }

    /**
     * @return Collection<int, int>
     */
    public function correctOptionIds(): Collection
    {
        return $this->options
            ->filter(fn (QuestionOption $option) => $option->is_correct)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();
    }

    public function hasUsableOptions(): bool
    {
        return $this->options->isNotEmpty() && $this->correctOptionIds()->isNotEmpty();
    }
}
