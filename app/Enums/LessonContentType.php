<?php

namespace App\Enums;

enum LessonContentType: string
{
    case UploadedVideo = 'uploaded_video';
    case ExternalLink = 'external_link';

    public function label(): string
    {
        return match ($this) {
            self::UploadedVideo => 'فيديو مرفوع',
            self::ExternalLink => 'رابط خارجي',
        };
    }
}
