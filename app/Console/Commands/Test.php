<?php

namespace App\Console\Commands;

use App\Facades\Spotify;
use App\Models\Category;
use App\Models\Genre;
use App\Models\User;
use App\Services\Tracker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle(Tracker $tracker)
    {
        $user = User::first();

        //dd($user->artists()->count());

        $refreshToken = $user->refresh_token;
        $accessToken = Spotify::getRefreshedAccessToken($refreshToken);

        //dd(Spotify::getSeveralAlbums($accessToken, ['4ixovByvCupbXWuy0bdeiM', '3SxQGioIMxgXrCAgzeIfer']));

        dd(Spotify::getLastArtistSingle($accessToken, '03Dy3XKBUsC3vJLCuF0T7I'));
//
       //$result = Spotify::getFollowedArtists($accessToken, '7fvTBshis8LPl6TrjnfOsl');
//
        //dd($result);

//        foreach (Genre::all() as $genre) {
//            $genre->categories()->detach();
//        }

//        $t1 = microtime(true);
//        $cat = $tracker->getGenreCategories('modern country rock');
//        $t2 = microtime(true);
//        $this->info(print_r($cat), true);
//        $this->info($t2 - $t1);
    }
}
