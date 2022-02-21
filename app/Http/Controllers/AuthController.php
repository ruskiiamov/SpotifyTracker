<?php

namespace App\Http\Controllers;

use App\Facades\Spotify;
use App\Http\Requests\CodeSpotifyRequest;
use App\Http\Requests\LoginSpotifyRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function loginSpotify(LoginSpotifyRequest $request)
    {
        $remember = $request->get('remember');
        session(['remember' => $remember]);

        $spotifyAuthUrl = Spotify::getAuthUrl();

        return redirect($spotifyAuthUrl);
    }

    public function callback(CodeSpotifyRequest $request)
    {
        $code = $request->get('code');

        $result = Spotify::getAccessToken($code);
        $userData = Spotify::getUserData($result->access_token);

        $email = $userData->email;
        $user = User::where('email', $email)->first();

        if (is_null($user)) {
            $user = new User();
            $user->fill([
                'name' => $userData->display_name,
                'email' => $email,
                'country' => $userData->country,
                'refresh_token' => $result->refresh_token,
            ])->save();
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
