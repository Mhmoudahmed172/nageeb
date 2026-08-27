<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'status', 'position'])]
class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
        ];
    }

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function lessonContents(): BelongsToMany
    {
        return $this->belongsToMany(LessonContent::class, 'lesson_content_region')->withTimestamps();
    }

    public function accessPlanPrices(): HasMany
    {
        return $this->hasMany(AccessPlanPrice::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Live)->orderBy('position');
    }

    public static function code(string $code): ?self
    {
        return static::query()->where('code', $code)->first();
    }
}
