<?php

namespace App\Support;

use App\Enums\ContentStatus;
use App\Enums\LessonContentType;
use App\Models\Lesson;
use App\Models\LessonContent;
use Illuminate\Http\UploadedFile;

final class VideoAsset
{
    public const STATE_UPLOADED = 'uploaded';

    public const STATE_PROCESSING = 'processing';

    public const STATE_READY = 'ready';

    public static function store(Lesson $lesson, UploadedFile $file, int $position): LessonContent
    {
        $path = $file->store('lessons/videos', 'public');

        return $lesson->contents()->create([
            'type' => LessonContentType::Video,
            'title' => $file->getClientOriginalName(),
            'position' => $position,
            'status' => ContentStatus::Live,
            'data' => [
                'path' => $path,
                'source' => 'upload',
                'state' => self::STATE_READY,
                'duration' => null,
                'original_name' => $file->getClientOriginalName(),
            ],
        ]);
    }

    public static function state(LessonContent $content): string
    {
        return (string) ($content->data['state'] ?? self::STATE_UPLOADED);
    }

    public static function stateLabel(LessonContent $content): string
    {
        return match (self::state($content)) {
            self::STATE_PROCESSING => 'جارٍ المعالجة',
            self::STATE_READY => 'جاهز',
            default => 'جارٍ الرفع',
        };
    }

    public static function durationLabel(LessonContent $content): ?string
    {
        $seconds = $content->data['duration'] ?? null;

        if (! is_numeric($seconds)) {
            return null;
        }

        $seconds = (int) $seconds;
        $minutes = intdiv($seconds, 60);
        $rest = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $rest);
    }
}
