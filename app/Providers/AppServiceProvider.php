<?php

namespace App\Providers;

use App\Models\Question;
use App\Observers\QuestionObserver;
use Barryvdh\Debugbar\Facades\Debugbar;
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
        app()->setLocale(session('locale', config('app.locale')));

        // Disable debugbar for non-authenticated users
        if (class_exists(Debugbar::class)) {
            if (! session('admin_authenticated', false)) {
                Debugbar::disable();
            }
        }

        //  Question::observe(QuestionObserver::class);
    }
}
