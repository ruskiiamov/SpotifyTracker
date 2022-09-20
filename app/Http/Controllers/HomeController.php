<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subscription;
use App\Services\Releases;
use App\Services\Tracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function followed(Request $request, Releases $releases)
    {
        $user = Auth::user();
        $onlyAlbums = $this->getOnlyAlbumsFlag($request);
        $page = $this->getPaginationPage($request);

        $albums = $releases->getFollowedAlbumsPaginator(
            user: $user,
            onlyAlbums: $onlyAlbums,
            page: $page
        );

        $newReleases = $albums->unique(function ($item) {
            return $item->name . $item->artist->id;
        })->groupby('release_date');

        return view('albums', [
            'newReleases' => $newReleases,
            'albums' => $albums,
            'categories' => [],
            'onlyAlbums' => $onlyAlbums,
            'current_route' => 'followed',
            'title' => 'Followed Artists',
        ]);
    }

    public function releases(Request $request, Tracker $tracker, Releases $releases)
    {
        $user = Auth::user();
        if (!empty($user)) {
            $country = $user->country;
            $categories = $user->categories;
            $categoryIds = $categories->pluck('id')->all();
        } else {
            $country = $tracker->getCountryCode($request);
            $categoryIds = session('subscriptions') ?? [];
            $categories = Category::whereIn('id', $categoryIds)->get();
        }

        $onlyAlbums = $this->getOnlyAlbumsFlag($request);
        $page = $this->getPaginationPage($request);

        $albums = $releases->getReleaseAlbumsPaginator(
            categoryIds: $categoryIds,
            country: $country,
            onlyAlbums: $onlyAlbums,
            page: $page
        );

        $newReleases = $albums->unique(function ($item) {
            return $item->name . $item->artist->id;
        })->groupby('release_date');

        return view('albums', [
            'newReleases' => $newReleases,
            'albums' => $albums,
            'categories' => $categories,
            'onlyAlbums' => $onlyAlbums,
            'current_route' => 'releases',
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

        try {
            $allCategories = Cache::remember('all_categories', config('spotifyConfig.cache_ttl'), function () {
                return Category::where('name', '<>', 'Other')->orderBy('name')->get();
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'method' => __METHOD__
            ]);
            $allCategories = Category::where('name', '<>', 'Other')->orderBy('name')->get();
        }

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
     * @param Request $request
     * @return int
     */
    private function getOnlyAlbumsFlag(Request $request): int
    {
        if ($request->exists('only_albums')) {
            if ($request->get('only_albums') == 1) {
                session(['only_albums' => 1]);
            } else {
                session(['only_albums' => 0]);
            }
        }

        return session('only_albums') ?? 0;
    }

    /**
     * @param Request $request
     * @return int
     */
    private function getPaginationPage(Request $request): int
    {
        if ($request->exists('page')) {
            return $request->get('page');
        }

        return 1;
    }
}
