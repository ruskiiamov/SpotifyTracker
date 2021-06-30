<?php

namespace App\Http\Controllers;

use App\Facades\Spotify;
use App\Models\Category;
use App\Models\User;
use App\Services\Tasks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function followed()
    {
        $user = Auth::user();
        $country = $user->country;
        $artists = $user->artists;
        $newReleases = [];
        foreach ($artists as $artist) {
            $albums = $artist->albums;
            if (is_null($albums->first())) {
                continue;
            }
            foreach ($albums as $album) {
                $markets = json_decode($album->markets);
                if (!in_array($country, $markets)) {
                    continue;
                }
                $newReleases[] = $album;
            }
        }
        foreach ($newReleases as $newRelease) {
            $artist = $newRelease->artist;
            echo "<a href='https://open.spotify.com/album/{$newRelease->spotify_id}'>{$artist->name} - {$newRelease->name}</a><br>";
        }
        die();
    }

    public function genres()
    {
        $user = Auth::user();
        $subscriptions = $user->subscriptions;
        $categories = Category::where('name', '<>', 'other')->get();
        return view('genres', ['subscriptions' => $subscriptions, 'categories' => $categories]);
    }

    public function saveSubscriptions()
    {
        //dump($request);
        echo 'hello';
        die();
    }
}
