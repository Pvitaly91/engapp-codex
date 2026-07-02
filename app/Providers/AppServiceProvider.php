<?php

namespace App\Providers;

use App\Http\Livewire\WordsTest;
use App\Models\Question;
use App\Observers\QuestionObserver;
use App\Support\SiteMode;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SiteMode::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale(session('locale', config('app.locale')));

        // Register Livewire components
        Livewire::component('words-test', WordsTest::class);

        Question::observe(QuestionObserver::class);

        Blade::if('devMode', fn (): bool => app(SiteMode::class)->isDevelopment());
        Blade::if('productionMode', fn (): bool => app(SiteMode::class)->isProduction());
        Blade::if('siteFeature', fn (string $feature): bool => app(SiteMode::class)->featureEnabled($feature));
    }
}
