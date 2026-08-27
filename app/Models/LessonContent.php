<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use Database\Factories\LessonContentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['lesson_id', 'type', 'title', 'position', 'data', 'status'])]
class LessonContent extends Model
{
    /** @use HasFactory<LessonContentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => LessonContentType::class,
            'status' => ContentStatus::class,
            'data' => 'array',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'lesson_content_region')->withTimestamps();
    }

    public function isAvailableToAllRegions(): bool
    {
        return $this->regions()->doesntExist();
    }

    public function fileUrl(): ?string
    {
        $path = $this->data['path'] ?? null;

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function displayName(): string
    {
        return $this->title
            ?: (string) ($this->data['name'] ?? $this->type->label());
    }
}
