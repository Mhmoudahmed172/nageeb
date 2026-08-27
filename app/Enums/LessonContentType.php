<?php

namespace App\Enums;

enum LessonContentType: string
{
    case Video = 'video';
    case Text = 'text';
    case File = 'file';
    case Audio = 'audio';
    case Link = 'link';
    case Quiz = 'quiz';
    case Assignment = 'assignment';
    case LiveSession = 'live_session';

    public function label(): string
    {
        return match ($this) {
            self::Video => 'فيديو',
            self::Text => 'نص',
            self::File => 'ملف',
            self::Audio => 'صوت',
            self::Link => 'رابط',
            self::Quiz => 'اختبار',
            self::Assignment => 'واجب',
            self::LiveSession => 'حصة مباشرة',
        };
    }
}
