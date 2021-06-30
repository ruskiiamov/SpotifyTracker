<?php

namespace App\Http\Controllers;

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

    public function checkAlbumList() {
        (new Tasks())->checkAlbumList();
        return redirect()->route('index');
    }
}
