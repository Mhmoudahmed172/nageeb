<?php

namespace App\Support;

use App\Models\Course;
use App\Models\User;

class NageebVisual
{
    public static function courseCover(?Course $course, int $index = 0): string
    {
        if ($course?->coverUrl()) {
            return $course->coverUrl();
        }

        return asset('images/nageeb/'.self::subjectImage($course?->title, $index));
    }

    public static function subjectImage(?string $title, int $index = 0): string
    {
        $haystack = (string) $title;

        return match (true) {
            str_contains($haystack, 'عربي') => 'courses/arabic.png',
            str_contains($haystack, 'رياض') => 'courses/mathematics.png',
            str_contains($haystack, 'فيزياء') => 'courses/physics.png',
            str_contains($haystack, 'كيمياء') => 'courses/chemistry.png',
            str_contains($haystack, 'إنجل') || str_contains($haystack, 'انجليز') => 'courses/english.png',
            str_contains($haystack, 'حاسوب') || str_contains($haystack, 'برمجة') => 'courses/computer.png',
            default => [
                'courses/dashboard-cover.png',
                'lessons/lesson-thumbnail.png',
                'exams/exam-thumbnail.png',
            ][$index % 3],
        };
    }

    public static function subjectLabel(?string $title): ?string
    {
        $haystack = (string) $title;

        return match (true) {
            str_contains($haystack, 'عربي') => 'لغة عربية',
            str_contains($haystack, 'رياض') => 'رياضيات',
            str_contains($haystack, 'فيزياء') => 'فيزياء',
            str_contains($haystack, 'كيمياء') => 'كيمياء',
            str_contains($haystack, 'إنجل') || str_contains($haystack, 'انجليز') => 'لغة إنجليزية',
            str_contains($haystack, 'حاسوب') || str_contains($haystack, 'برمجة') => 'حاسوب',
            default => null,
        };
    }

    public static function teacherPhoto(?User $teacher): string
    {
        $src = $teacher?->teacherProfile?->avatarSrc();

        if (filled($src)) {
            return $src;
        }

        return asset('images/nageeb/teachers/placeholder.png');
    }

    public static function emptyImage(string $title): string
    {
        return match (true) {
            str_contains($title, 'اختبار') => 'illustrations/exams.png',
            str_contains($title, 'اشتراك') || str_contains($title, 'سحب') => 'illustrations/learning.png',
            str_contains($title, 'شهادة') || str_contains($title, 'إنجاز') => 'illustrations/certificate.png',
            str_contains($title, 'تعلّم') || str_contains($title, 'درس') || str_contains($title, 'نشاط') => 'illustrations/progress.png',
            str_contains($title, 'إشعار') => 'illustrations/empty.png',
            default => 'illustrations/empty.png',
        };
    }
}
