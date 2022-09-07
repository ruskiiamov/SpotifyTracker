<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Category;
use App\Models\Connection;
use App\Models\Genre;
use App\Models\Subscription;
use App\Services\IpInfo;
use App\Services\Tracker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function followed(Request $request)
    {
        $user = Auth::user();
        $country = $user->country;

        $artistIds = $user->artists()->has('albums')->get()->pluck('id')->all();

        $only_albums = $this->getOnlyAlbumsFlag($request);

        $albums = Album::whereIn('artist_id', $artistIds)
            ->whereJsonContains('markets', $country)
            ->when($only_albums, function ($query) {
                $query->where('type', 'album');
            })
            ->orderBy('release_date', 'desc')
            ->orderBy('popularity', 'desc')
            ->paginate(config('spotifyConfig.pagination'));

        $newReleases = $albums->unique(function ($item) {
            return $item['name'] . $item['artist_id'];
        })->groupby('release_date');


        return view('albums', [
            'newReleases' => $newReleases,
            'albums' => $albums,
            'categories' => [],
            'only_albums' => $only_albums,
            'current_route' => 'followed',
            'title' => 'Followed Artists',
        ]);
    }

    public function releases(Request $request, IpInfo $location, Tracker $tracker)
    {
        $user = Auth::user();
        if (!empty($user)) {
            $country = $user->country;
            $categoryIds = $user->categories->pluck('id')->all();
            $categories = $user->categories;
        } else {
            $country = $this->processCountryCode($tracker, $location->getCountryCode($request));
            $categoryIds = session('subscriptions') ?? [];
            $categories = Category::whereIn('id', $categoryIds)->get();
        }

        $genreIds = Genre::whereHas('categories', function (Builder $query) use ($categoryIds) {
            $query->whereIn('id', $categoryIds);
        })->get()->unique('id')->pluck('id')->all();

        $artistIds = Connection::whereIn('genre_id', $genreIds)->get()->unique('artist_id')->pluck('artist_id')->all();

        $only_albums = $this->getOnlyAlbumsFlag($request);

        $albums = Album::whereIn('artist_id', $artistIds)
            ->whereJsonContains('markets', $country)
            ->when($only_albums, function ($query) {
                $query->where('type', 'album');
            })
            ->orderBy('release_date', 'desc')
            ->orderBy('popularity', 'desc')
            ->paginate(config('spotifyConfig.pagination'));

        $newReleases = $albums->unique(function ($item) {
            return $item['name'] . $item['artist_id'];
        })->groupby('release_date');

        return view('albums', [
            'newReleases' => $newReleases,
            'albums' => $albums,
            'categories' => $categories,
            'only_albums' => $only_albums,
            'current_route' => 'releases',
            'title' => 'Releases by Genre',
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

        return redirect()->route('releases');
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

    /**
     * @param Request $request
     * @return bool
     */
    private function getOnlyAlbumsFlag(Request $request): bool
    {
        if ($request->exists('only_albums')) {
            if ($request->get('only_albums') == 1) {
                session(['only_albums' => true]);
            } else {
                session(['only_albums' => false]);
            }
        }

        return session('only_albums') ?? false;
    }
}
