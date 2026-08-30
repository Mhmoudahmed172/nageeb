<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\ExternalUrl;
use Illuminate\Support\Collection;

#[Fillable([
    'unit_id',
    'title',
    'description',
    'position',
    'status',
    'is_preview',
    'estimated_duration',
])]
class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'is_preview' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function belongsToCourse(Course $course): bool
    {
        return $this->unit?->semester?->course_id === $course->id;
    }

    public function contents(): HasMany
    {
        return $this->hasMany(LessonContent::class)->orderBy('position');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function videoContents(): Collection
    {
        return $this->contents->filter(
            fn (LessonContent $content) => $content->type === LessonContentType::Video,
        )->values();
    }

    public function nextContentPosition(): int
    {
        return (int) $this->contents()->max('position') + 1;
    }

    public function fileContents(): Collection
    {
        return $this->contents->filter(
            fn (LessonContent $content) => $content->type === LessonContentType::File,
        )->values();
    }

    public function playbackContent(): ?LessonContent
    {
        return $this->contents->first(
            fn (LessonContent $content) => in_array(
                $content->type,
                [LessonContentType::Video, LessonContentType::Link],
                true,
            ),
        );
    }

    public function videoUrl(): ?string
    {
        $block = $this->playbackContent();

        if ($block?->type !== LessonContentType::Video) {
            return null;
        }

        return $block->accessUrl();
    }

    public function embedUrl(): ?string
    {
        $block = $this->playbackContent();
        $url = $block?->data['url'] ?? null;

        if (! $url) {
            return null;
        }

        return ExternalUrl::youtubeEmbed($url) ?? $url;
    }
}
