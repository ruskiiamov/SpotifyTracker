<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Category;
use App\Models\Connection;
use App\Models\Genre;
use App\Models\Subscription;
use App\Services\IpInfo;
use App\Services\Tracker;
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
        return view('albums', [
            'newReleases' => $newReleases,
            'albums' => $albums,
            'categories' => [],
            'title' => $title]);
    }

    public function subscribed(Request $request, IpInfo $location, Tracker $tracker)
    {
        $user = Auth::user();
        if (!empty($user)) {
            $country = $user->country;
            $categoryIds = $user->categories->pluck('id')->all();
            $categories = $user->categories;
            $followedArtistIds = $user->artists()->has('albums')->get()->pluck('id')->all();
        } else {
            $country = $this->processCountryCode($tracker, $location->getCountryCode($request));
            $categoryIds = session('subscriptions') ?? [];
            $categories = Category::whereIn('id', $categoryIds)->get();
            $followedArtistIds = [];
        }

        $genreIds = Genre::whereIn('category_id', $categoryIds)->get()->pluck('id')->all();
        $artistIds = Connection::whereIn('genre_id', $genreIds)->get()->unique('artist_id')->pluck('artist_id')->all();
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

        return view('albums', [
            'newReleases' => $newReleases,
            'albums' => $albums,
            'categories' => $categories,
            'title' => $title
        ]);
    }

    public function genres()
    {
        $user = Auth::user();

        if (!empty($user)) {
            $userCategories = $user->categories;
        } else {
            $subscriptions = session('subscriptions') ?? [];
            $userCategories = Category::whereIn('id', $subscriptions)->get();
        }

        $allCategories = Category::where('name', '<>', 'Other')->orderBy('name')->get();
        return view('genres', ['userCategories' => $userCategories, 'allCategories' => $allCategories]);
    }

    public function saveSubscriptions(Request $request)
    {
        $user = Auth::user();

        if (!empty($user)) {
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
        } else {
            $subscriptions = [];
            foreach ($request->except('_token') as $categoryId => $value) {
                if ($value == 1) {
                    $subscriptions[] = $categoryId;
                }
            }
            session(['subscriptions' => $subscriptions]);
        }

        return redirect()->route('subscribed');
    }

    /**
     * @param Tracker $tracker
     * @param string|null $countryCode
     * @return string
     */
    private function processCountryCode(Tracker $tracker, string $countryCode = null): string
    {
        if (!isset($countryCode) || !in_array($countryCode, $tracker->getCurrentMarkets())) {
            return config('spotifyConfig.default_market');
        }

        return $countryCode;
    }
}
