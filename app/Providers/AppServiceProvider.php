<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Schedule;
use App\Models\Task;
use App\Policies\OwnerPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Task::class, Course::class, Schedule::class] as $model) {
            Gate::policy($model, OwnerPolicy::class);
        }
    }
}
