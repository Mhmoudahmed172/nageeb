<?php

namespace App\Providers;

use App\Models\Course;
use App\Policies\CoursePolicy;
use App\Support\ContentAccess;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentAccess::class);
    }

    public function boot(): void
    {
        Gate::policy(Course::class, CoursePolicy::class);
    }
}
