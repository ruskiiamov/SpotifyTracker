<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body class="bg-black text-white text-lg min-h-screen flex flex-col justify-between font-sans">
<header class="bg-green text-white mb-6">
    <div class="container mx-auto px-4 flex justify-between items-center flex-wrap">
        <div class="flex items-center py-3">
            <a href="{{ route('index') }}" class="text-3xl ">Release</a>
            <a href="{{ route('index') }}" class="font-bold text-3xl ">Hunter</a>
        </div>
        <div class="flex items-center py-2">{{ $info }}</div>
        <div class="flex items-center py-2">{{ $auth }}</div>
    </div>
</header>
<main>
    <div class="container mx-auto px-4">
        {{ $slot }}
    </div>
</main>
<footer>
    <div class="container mx-auto px-4 flex justify-between items-center flex-wrap">
        <div class="py-6 flex-none">
            <a href="https://www.spotify.com/">
                <img src="{{ asset('storage/Spotify_Logo_RGB_White.png') }}" class="h-12">
            </a>
        </div>
        <div class="py-6 flex-none">
            <a href="https://github.com/ruskiiamov/ReleaseHunter-for-Spotify">
                <img src="{{ asset('storage/GitHub_Logo_White.png') }}" class="h-12">
            </a>
        </div>
    </div>
</footer>
</body>
</html>
