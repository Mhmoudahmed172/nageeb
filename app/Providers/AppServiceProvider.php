<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Policies\CoursePolicy;
use App\Policies\ExamPolicy;
use App\Policies\QuestionPolicy;
use App\Support\ContentAccess;
use App\Support\ExamAccess;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentAccess::class);
        $this->app->singleton(ExamAccess::class);
        $this->app->singleton(\App\Support\MediaStore::class);
    }

    public function boot(): void
    {
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(Question::class, QuestionPolicy::class);

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceHttps();
        }
    }
}
