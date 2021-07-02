<?php

namespace App\Http\Controllers;

use App\Facades\Spotify;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Tasks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

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
                $newReleases[$album->release_date][$album->popularity] = $album;
            }
        }
        krsort($newReleases);
        foreach ($newReleases as $item) {
            krsort($item);
        }
        return view('followed', ['newReleases' => $newReleases]);
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
            echo "{$key} => {$item}<br>";
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

        return redirect()->route('index');
    }
}
