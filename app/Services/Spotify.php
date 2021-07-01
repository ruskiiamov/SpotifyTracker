<?php


namespace App\Services;


use Illuminate\Support\Facades\Auth;

class Spotify
{

    private $authUrl = 'https://accounts.spotify.com/authorize';
    private $tokenUrl = 'https://accounts.spotify.com/api/token';
    private $apiUrl = 'https://api.spotify.com/v1';
    private $scope = 'user-read-email user-read-private user-follow-read user-library-read';


    public function getAuthUrl()
    {
        $parameters = [
            'client_id' => env('SPOTIFY_CLIENT_ID'),
            'response_type' => 'code',
            'redirect_uri' => route('callback'),
            'scope' => $this->scope,
            'state' => $this->createState(),
            'show_dialog' => 'true',
        ];
        $url = $this->authUrl . '?' . http_build_query($parameters, '', '&');
        return $url;
    }


    private function createState()
    {
        $state = uniqid(rand(), true);
        session(['state' => $state]);
        return $state;
    }


    public function getAccessToken($code)
    {
        $parameters = [
            'client_id' => env('SPOTIFY_CLIENT_ID'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => route('callback'),
            'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
        ];

        $result = $this->request('POST', $this->tokenUrl, $parameters);

        $this->saveAccessToken($result->access_token, $result->expires_in);

        return $result;

    }


    public function getRefreshedAccessToken($refreshToken)
    {
        $parameters = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        $base64 = base64_encode(env('SPOTIFY_CLIENT_ID') . ':' . env('SPOTIFY_CLIENT_SECRET'));

        $headers = ['Authorization' => 'Basic ' . $base64];

        $result = $this->request('POST', $this->tokenUrl, $parameters, $headers);

        $this->saveAccessToken($result->access_token, $result->expires_in);

        return $result->access_token;
    }


    public function getUserData($accessToken)
    {
        $headers = ['Authorization' => 'Bearer ' . $accessToken];
        return $this->request('GET', $this->apiUrl . '/me', [], $headers);
    }


    private function request($method, $url, $parameters = [], $headers = [])
    {
        $parameters = http_build_query($parameters, '', '&');

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [],
        ];

        foreach ($headers as $key => $val) {
            $options[CURLOPT_HTTPHEADER][] = "{$key}: {$val}";
        }

        if ($method == 'GET') {
            $options[CURLOPT_URL] = $url . $parameters;
        }

        if ($method == 'POST') {
            $options[CURLOPT_URL] = $url;
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $parameters;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        return $response;
    }

    private function saveAccessToken($accessToken, $expires_in)
    {
        session([
            'access_token' => $accessToken,
            'expiring_time' => time() + (int) (0.99 * $expires_in),
        ]);
    }


    public function isFreshAccessToken()
    {
        return time() < session('expiring_time');
    }


    public function getFollowedArtists($accessToken, $after = null)
    {
        $parameters = [
            'type' => 'artist',
            'limit' => '50',
        ];

        if ($after) {
            $parameters['after'] = $after;
        }

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . '/me/following?', $parameters, $headers);
    }

    public function getSavedAlbums($accessToken, $offset = null)
    {
        $parameters = [
            'limit' => '50',
        ];

        if ($offset) {
            $parameters['offset'] = $offset;
        }

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . '/me/albums?', $parameters, $headers);
    }

    public function getLastArtistAlbum($accessToken, $artistId)
    {
        $parameters = [
            'include_groups' => 'album',
            'limit' => 1,
        ];

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . "/artists/{$artistId}/albums?", $parameters, $headers);
    }

    public function getArtistAlbums($accessToken, $artistId)
    {
        $parameters = [
            'include_groups' => 'album',
        ];

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . "/artists/{$artistId}/albums?", $parameters, $headers);
    }

    public function getAlbum($accessToken, $albumId)
    {
        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . "/albums/{$albumId}", [], $headers);

    }

    public function getArtist($accessToken, $artistId)
    {
        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . "/artists/{$artistId}?", [], $headers);
    }

    public function getNewReleases($accessToken, $offset = null)
    {
        $parameters = [
            'q' => 'tag:new',
            'type' => 'album',
            'limit' => '50',
        ];

        if ($offset) {
            $parameters['offset'] = $offset;
        }

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . '/search?', $parameters, $headers);
    }
}
