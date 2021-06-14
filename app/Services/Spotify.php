<?php


namespace App\Services;


class Spotify
{
    private $authUrl = 'https://accounts.spotify.com/authorize';
    private $tokenUrl = 'https://accounts.spotify.com/api/token';
    private $apiUrl = 'https://api.spotify.com/v1';
    private $scope = 'user-read-email user-read-private user-follow-read';

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

        return $this->request('POST', $this->tokenUrl, $parameters);

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
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [],
        ];

        foreach ($headers as $key => $val) {
            $options[CURLOPT_HTTPHEADER][] = "{$key}: {$val}";
        }

        if ($method == 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $parameters;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        return $response;
    }
}
