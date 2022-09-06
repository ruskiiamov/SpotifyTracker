<?php

namespace App\Providers;

use App\Interfaces\GenreCategorizerInterface;
use App\Services\GenreCategorizer;
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
                getSeveralAlbumsLimit: config('spotifyConfig.getSeveralAlbumsLimit'),
                getSeveralArtistsLimit: config('spotifyConfig.getSeveralArtistsLimit'),
                releaseAge: config('spotifyConfig.releaseAge'),
                exceptions: config('spotifyConfig.exceptions'),
                artistIdExceptions: config('spotifyConfig.artistIdExceptions'),
                bannedGenreNames: config('genres.bannedGenreNames'),
                genreCategorizer: $this->app->make(GenreCategorizerInterface::class)
            );
        });

        $this->app->singleton(GenreCategorizerInterface::class, function () {
            return new GenreCategorizer(
                regularKeyWords: config('genres.regularKeyWords'),
                specialKeyWords: config('genres.specialKeyWords'),
                bannedGenreNames: config('genres.bannedGenreNames'),
                other: config('genres.other')
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
