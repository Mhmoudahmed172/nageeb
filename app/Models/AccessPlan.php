<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Database\Factories\AccessPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'course_id',
    'title',
    'description',
    'status',
    'access_duration_days',
    'starts_at',
    'ends_at',
])]
class AccessPlan extends Model
{
    /** @use HasFactory<AccessPlanFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function semesters(): BelongsToMany
    {
        return $this->belongsToMany(Semester::class, 'access_plan_semester')->withTimestamps();
    }

    public function prices(): HasMany
    {
        return $this->hasMany(AccessPlanPrice::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function priceFor(?Region $region): ?AccessPlanPrice
    {
        if (! $region) {
            return null;
        }

        return $this->prices()->where('region_id', $region->id)->first();
    }

    public function unlocksSemester(int $semesterId): bool
    {
        return $this->semesters->contains('id', $semesterId);
    }
}
