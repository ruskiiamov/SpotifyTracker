<?php

namespace App\Http\Controllers;

use App\Facades\Spotify;
use App\Models\User;
use App\Services\Tasks;

class AdminController extends Controller
{
    public function updateFollowedArtists()
    {
        (new Tasks())->updateFollowedArtists();
        return redirect()->route('index');
    }

    public function updateAlbumList()
    {
        (new Tasks())->updateAlbumList();
        return redirect()->route('index');
    }

    public function genresAnalyse()
    {
        (new Tasks())->genresAnalyse();
        die();
    }

    public function checkAlbumList()
    {
        (new Tasks())->checkAlbumList();
        return redirect()->route('index');
    }

    public function checkArtistList()
    {
        (new Tasks())->checkArtistList();
        return redirect()->route('index');
    }

    public function addNewReleases()
    {
        (new Tasks())->addNewReleases();
        return redirect()->route('index');
    }

    public function test()
    {
        $refreshToken = User::first()->refresh_token;
        $accessToken = Spotify::getRefreshedAccessToken($refreshToken);

        $result = Spotify::getAlbum($accessToken, '0doIEoeVI4j8R1hoQ7eHA1');

        dump($result);
        die();
    }
}
