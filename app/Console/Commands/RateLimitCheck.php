<?php

namespace App\Console\Commands;

use App\Exceptions\SpotifyRequestException;
use App\Facades\Spotify;
use App\Models\Artist;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RateLimitCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rate-limit-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check rate limit; if OK then remove restrictions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        if (!Spotify::areRequestsAvailable()) {
            try {
                if (Cache::has('client_access_token')) {
                    $accessToken = Cache::get('client_access_token');
                } else {
                    $accessToken = Spotify::getClientAccessToken();
                }

                if (Artist::first()->exists()) {
                    Spotify::getArtist($accessToken, Artist::first()->spotify_id);
                } else {
                    Spotify::getMarkets();
                }
            } catch (SpotifyRequestException $se) {
                return;
            }

            Cache::forget('spotify-requests-available-since');
        }
    }
}
