<?php

namespace App\Models;

use App\Enums\CourseStatus;
use App\Enums\GradeLevel;
use App\Enums\ContentStatus;
use App\Support\ContentAccess;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'teacher_id',
    'title',
    'slug',
    'description',
    'grade_level',
    'status',
    'is_free',
    'reference_price',
    'cover_image',
])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'grade_level' => GradeLevel::class,
            'status' => CourseStatus::class,
            'is_free' => 'boolean',
            'reference_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Course $course): void {
            if (filled($course->slug)) {
                return;
            }

            $course->slug = static::uniqueSlugFromTitle($course->title);
        });

        static::created(function (Course $course): void {
            if ($course->semesters()->doesntExist()) {
                $course->semesters()->create([
                    'title' => 'الفصل الدراسي',
                    'position' => 1,
                    'status' => ContentStatus::Live,
                ]);
            }
        });
    }

    public static function uniqueSlugFromTitle(string $title): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'course';
        }

        $slug = $base;
        $suffix = 1;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class)->orderBy('position');
    }

    public function accessPlans(): HasMany
    {
        return $this->hasMany(AccessPlan::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(SubscriptionPackage::class);
    }

    public function subscriptionRequests(): HasMany
    {
        return $this->hasMany(SubscriptionRequest::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function units(): HasManyThrough
    {
        return $this->hasManyThrough(Unit::class, Semester::class)->orderBy('units.position');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CourseAttachment::class)->orderBy('position');
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        $field ??= 'id';

        return match ($childType) {
            'semester', 'semesters' => $this->semesters()->where($field, $value)->first(),
            'unit', 'units' => $this->units()->where('units.'.$field, $value)->first(),
            'lesson', 'lessons' => Lesson::query()
                ->where($field, $value)
                ->whereHas('unit.semester', fn (Builder $query) => $query->where('course_id', $this->id))
                ->first(),
            'access_plan', 'accessPlan', 'accessPlans', 'package', 'packages' => $this->accessPlans()->where($field, $value)->first(),
            default => parent::resolveChildRouteBinding($childType, $value, $field),
        };
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image
            ? Storage::disk('public')->url($this->cover_image)
            : null;
    }

    public function isFreeForStudents(): bool
    {
        return $this->is_free && $this->status === CourseStatus::Live;
    }

    public function studentHasAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return app(ContentAccess::class)->studentCanAccessCourse($user, $this);
    }

    public function isOwnedBy(int $teacherId): bool
    {
        return $this->teacher_id === $teacherId;
    }

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function defaultSemester(): Semester
    {
        return $this->semesters()->orderBy('position')->firstOrFail();
    }

    public function nextSemesterPosition(): int
    {
        return (int) $this->semesters()->max('position') + 1;
    }

    public function nextUnitPosition(): int
    {
        return $this->defaultSemester()->nextUnitPosition();
    }

    public function suggestedUnitTitle(): string
    {
        $ordinals = [
            1 => 'الأولى',
            2 => 'الثانية',
            3 => 'الثالثة',
            4 => 'الرابعة',
            5 => 'الخامسة',
            6 => 'السادسة',
            7 => 'السابعة',
            8 => 'الثامنة',
            9 => 'التاسعة',
            10 => 'العاشرة',
        ];

        $next = $this->units()->count() + 1;

        return 'الوحدة '.($ordinals[$next] ?? $next);
    }

    public function resequenceUnits(): void
    {
        $this->semesters->each->resequenceUnits();
    }
}
