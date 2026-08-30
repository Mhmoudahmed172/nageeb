<?php

namespace App\Models;

use App\Enums\AttemptStatus;
use Database\Factories\ExamAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'exam_id',
    'student_id',
    'attempt_number',
    'status',
    'started_at',
    'expires_at',
    'submitted_at',
    'score',
    'total_points',
    'percentage',
    'passed',
])]
class ExamAttempt extends Model
{
    /** @use HasFactory<ExamAttemptFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => AttemptStatus::class,
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
            'total_points' => 'decimal:2',
            'percentage' => 'decimal:2',
            'passed' => 'boolean',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function isInProgress(): bool
    {
        return $this->status === AttemptStatus::InProgress;
    }

    public function hasTimedOut(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function secondsRemaining(): ?int
    {
        if ($this->expires_at === null) {
            return null;
        }

        return max(0, now()->diffInSeconds($this->expires_at, false));
    }
}
