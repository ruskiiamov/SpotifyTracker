<?php

namespace App\Providers;

use App\Contracts\SpotifyApiClientInterface;
use App\Contracts\TrackerInterface;
use App\Services\SpotifyApi\SpotifyApiClient;
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
        $this->app->bind(SpotifyApiClientInterface::class, SpotifyApiClient::class);
        $this->app->bind(TrackerInterface::class, Tracker::class);
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
