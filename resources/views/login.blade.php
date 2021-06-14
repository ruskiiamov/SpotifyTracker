<x-layout>
    <x-slot name="header">
        <h2>
            {{ __('Login') }}
        </h2>
    </x-slot>

    <div>
        <p>Please login with your Spotify account to track your new releases!</p>
        <form method="post" action="{{ route('loginSpotify') }}">
            @csrf
            <input type="hidden" name="remember" value="0">
            <input type="checkbox" name="remember" value="1"><span>Remember me</span><br>
            <input type="submit" value="Login with Spotify">
        </form>
    </div>
</x-layout>
