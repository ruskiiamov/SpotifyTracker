<?php

namespace App\Http\Controllers;

use App\Facades\Spotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function followed()
    {
        $timemark1 = time();

        if (!Spotify::isFreshAccessToken()) {
            $refreshToken = Auth::user()->refresh_token;
            Spotify::getRefreshedAccessToken($refreshToken);
        }

        $followedArtistsId = [];
        $after = null;
        do {
            $result = Spotify::getFollowedArtists(session('access_token'), $after);
            $artists = $result->artists->items;
            foreach ($artists as $artist) {
                $followedArtistsId[] = $artist->id;
            }
            $after = $result->artists->cursors->after;
        } while ($after);

        $timemark2 = time();

        $twoWeeksAgoDate = date('Y-m-d', time() - 14 * 24 * 60 * 60);

        $newAlbumsId = [];

        foreach ($followedArtistsId as $item) {
            $album = Spotify::getLastArtistAlbum(session('access_token'), $item);
            if (empty($album->items)) {
                continue;
            }
            $releaseDate = $album->items[0]->release_date;
            if ($releaseDate >= $twoWeeksAgoDate) {
                $newAlbumsId[] = $album->items[0]->id;
            }
        }

        dump($newAlbumsId);

        $timemark3 = time();

        dump($timemark2 - $timemark1);
        dump($timemark3 - $timemark2);

        die();
    }
}
