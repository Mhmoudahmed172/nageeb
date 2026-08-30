<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

final class ExternalUrl
{
    public static function assert(string $url): string
    {
        $url = trim($url);

        if ($url === '' || mb_strlen($url) > 2048) {
            throw ValidationException::withMessages([
                'url' => 'الرابط غير صالح.',
            ]);
        }

        $parts = parse_url($url);

        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            throw ValidationException::withMessages([
                'url' => 'الرابط غير صالح.',
            ]);
        }

        $scheme = strtolower((string) $parts['scheme']);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                'url' => 'يُسمح فقط بروابط http أو https.',
            ]);
        }

        return $url;
    }

    public static function youtubeEmbed(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]+)~', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('~youtube\.com/embed/([A-Za-z0-9_-]+)~', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }
}
