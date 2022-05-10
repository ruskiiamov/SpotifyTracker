<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Category;
use App\Models\Connection;
use App\Models\Genre;
use App\Models\Subscription;
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
        $user = Auth::user();
        $country = $user->country;

        $artistIds = $user->artists()->has('albums')->get()->pluck('id')->all();

        $albums = Album::whereIn('artist_id', $artistIds)
            ->whereJsonContains('markets', $country)
            ->orderBy('release_date', 'desc')
            ->orderBy('popularity', 'desc')
            ->paginate(config('spotifyConfig.pagination'));

        $newReleases = $albums->unique(function ($item) {
            return $item['name'] . $item['artist_id'];
        })->groupby('release_date');

        $title = 'followed artists';
        return view('albums', ['newReleases' => $newReleases, 'albums' => $albums, 'title' => $title]);
    }

    public function subscribed()
    {
        $user = Auth::user();
        $country = $user->country;

        $categoryIds = $user->categories->pluck('id')->all();
        $genreIds = Genre::whereIn('category_id', $categoryIds)->get()->pluck('id')->all();
        $artistIds = Connection::whereIn('genre_id', $genreIds)->get()->unique('artist_id')->pluck('artist_id')->all();
        $followedArtistIds = $user->artists()->has('albums')->get()->pluck('id')->all();
        $filteredArtistIds = array_diff($artistIds, $followedArtistIds);

        $albums = Album::whereIn('artist_id', $filteredArtistIds)
            ->whereJsonContains('markets', $country)
            ->orderBy('release_date', 'desc')
            ->orderBy('popularity', 'desc')
            ->paginate(config('spotifyConfig.pagination'));

        $newReleases = $albums->unique(function ($item) {
            return $item['name'] . $item['artist_id'];
        })->groupby('release_date');

        $title = 'subscribed genres';

        return view('albums', ['newReleases' => $newReleases, 'albums' => $albums, 'title' => $title]);
    }

    public function genres()
    {
        $user = Auth::user();
        $subscriptions = $user->subscriptions;
        $categories = Category::where('name', '<>', 'other')->orderBy('name')->get();
        return view('genres', ['subscriptions' => $subscriptions, 'categories' => $categories]);
    }

    public function saveSubscriptions(Request $request)
    {
        $user = Auth::user();

        foreach ($request->except('_token') as $key => $item) {
            if ($item) {
                Subscription::firstOrCreate([
                    'user_id' => $user->id,
                    'category_id' => $key,
                ]);
            } else {
                $subscription = Subscription::where('user_id', $user->id)->where('category_id', $key)->first();
                if (!is_null($subscription)) {
                    $subscription->delete();
                }
            }
        }

        return redirect()->route('subscribed');
    }
}
