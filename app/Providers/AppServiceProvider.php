<?php

namespace App\Providers;

use App\Services\Tracker;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Tracker::class, function () {
            return new Tracker(
                releaseAge: config('spotifyConfig.releaseAge'),
                genreCategories: config('spotifyConfig.genreCategories'),
                exceptions: config('spotifyConfig.exceptions'),
                artistIdExceptions: config('spotifyConfig.artistIdExceptions'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
