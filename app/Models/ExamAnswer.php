<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'exam_attempt_id',
    'question_id',
    'selected_option_ids',
    'text_answer',
    'is_correct',
    'points_awarded',
])]
class ExamAnswer extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array',
            'is_correct' => 'boolean',
            'points_awarded' => 'decimal:2',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return array<int, int>
     */
    public function selectedIds(): array
    {
        return array_map('intval', $this->selected_option_ids ?? []);
    }
}
