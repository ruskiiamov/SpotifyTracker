<?php

return [
    'client_id' => env('SPOTIFY_CLIENT_ID'),

    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),

    'authUrl' => 'https://accounts.spotify.com/authorize',

    'tokenUrl' => 'https://accounts.spotify.com/api/token',

    'apiUrl' => 'https://api.spotify.com/v1',

    'scope' => 'user-read-email user-read-private user-follow-read user-library-read',
];
