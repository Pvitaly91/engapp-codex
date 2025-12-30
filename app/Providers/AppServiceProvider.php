<?php

namespace App\Providers;

use App\Http\Livewire\WordsTest;
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
        // Register Livewire components
        Livewire::component('words-test', WordsTest::class);

        //  Question::observe(QuestionObserver::class);
    }
}
