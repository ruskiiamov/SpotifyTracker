<?php

namespace App\Http\Controllers;

use App\Facades\Spotify;
use App\Http\Requests\CodeSpotifyRequest;
use App\Http\Requests\LoginSpotifyRequest;
use App\Jobs\AfterFirstLogin;
use App\Models\User;
use App\Services\Tracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function loginSpotify(LoginSpotifyRequest $request, Tracker $tracker)
    {
        $remember = $request->get('remember');
        session(['remember' => $remember]);
        $tracker->getCountryCode($request);

        $spotifyAuthUrl = Spotify::getAuthUrl();

        return redirect($spotifyAuthUrl);
    }

    public function callback(CodeSpotifyRequest $request, Tracker $tracker)
    {
        $code = $request->get('code');

        $result = Spotify::getAccessToken($code);
        $userData = Spotify::getUserData($result->access_token);

        $user = User::where('spotify_id', $userData->id)->first();

        if (empty($user)) {
            $user = User::create([
                'name' => $userData->display_name,
                'spotify_id' => $userData->id,
                'country' => $tracker->getCountryCode($request),
                'refresh_token' => $result->refresh_token,
            ]);

            AfterFirstLogin::dispatch($user)->onQueue('high');
            Auth::login($user, session('remember'));
            return redirect()->route('genres');
        } else {
            $country = $tracker->getCountryCode($request);
            if ($user->country != $country) {
                $user->update(['country' => $country]);
            }
        }

        Auth::login($user, session('remember'));
        return redirect()->route('index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
