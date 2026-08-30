<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PHP empties $_POST/$_FILES when the body exceeds post_max_size, which Laravel
 * then reports as a CSRF 419. Intercept that case before the token check.
 */
class DetectOversizedUploads
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            return $next($request);
        }

        $contentLength = (int) ($request->server('CONTENT_LENGTH') ?: $request->header('Content-Length', 0));
        $postMax = $this->iniBytes((string) ini_get('post_max_size'));

        $exceedsIni = $postMax > 0 && $contentLength > $postMax;
        $phpDiscardedBody = $contentLength > 1024
            && $request->files->count() === 0
            && count($request->request->all()) === 0
            && str_contains((string) $request->header('Content-Type'), 'multipart/form-data');

        if (! $exceedsIni && ! $phpDiscardedBody) {
            return $next($request);
        }

        $payload = [
            'message' => 'حجم الملف أكبر من الحد المسموح.',
            'code' => 'too_large',
        ];

        if ($request->expectsJson() || str_contains((string) $request->header('Accept'), 'json')) {
            return response()->json($payload, 413);
        }

        abort(413, $payload['message']);
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '0') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }
}
