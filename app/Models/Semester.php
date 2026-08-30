<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Database\Factories\SemesterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_id', 'title', 'description', 'position', 'status'])]
class Semester extends Model
{
    /** @use HasFactory<SemesterFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class)->orderBy('position');
    }

    public function accessPlans(): BelongsToMany
    {
        return $this->belongsToMany(AccessPlan::class, 'access_plan_semester')->withTimestamps();
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function nextUnitPosition(): int
    {
        return (int) $this->units()->max('position') + 1;
    }

    public function resequenceUnits(): void
    {
        $units = $this->units()->orderBy('position')->orderBy('id')->get();
        $offset = $units->count() + 1000;

        foreach ($units as $index => $unit) {
            $unit->update(['position' => $offset + $index]);
        }

        foreach ($units as $index => $unit) {
            $unit->update(['position' => $index + 1]);
        }
    }
}
