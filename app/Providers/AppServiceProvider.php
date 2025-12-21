<?php

namespace App\Providers;

use App\Models\Question;
use App\Observers\QuestionObserver;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        //  Question::observe(QuestionObserver::class);

        // Register Livewire components
        Livewire::component('words.public-test', \App\Livewire\Words\PublicTest::class);
    }
}
