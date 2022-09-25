<?php

namespace App\Services;

use App\Exceptions\SpotifyRequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Exception;

class Spotify
{
    private string $authUrl = 'https://accounts.spotify.com/authorize';
    private string $tokenUrl = 'https://accounts.spotify.com/api/token';
    private string $apiUrl = 'https://api.spotify.com/v1';
    private string $scope = 'user-follow-read';

    public function getAuthUrl()
    {
        $parameters = [
            'client_id' => config('spotifyConfig.client_id'),
            'response_type' => 'code',
            'redirect_uri' => route('callback'),
            'scope' => $this->scope,
            'state' => $this->createState(),
            'show_dialog' => 'true',
        ];
        $url = $this->authUrl . '?' . http_build_query($parameters, '', '&');
        return $url;
    }

    public function getAccessToken($code)
    {
        $parameters = [
            'client_id' => config('spotifyConfig.client_id'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => route('callback'),
            'client_secret' => config('spotifyConfig.client_secret'),
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

        $base64 = base64_encode(config('spotifyConfig.client_id') . ':' . config('spotifyConfig.client_secret'));

        $headers = ['Authorization' => 'Basic ' . $base64];

        $result = $this->request('POST', $this->tokenUrl, $parameters, $headers);

        $this->saveAccessToken($result->access_token, $result->expires_in);

        return $result->access_token;
    }

    public function getClientAccessToken()
    {
        $parameters = [
            'grant_type' => 'client_credentials',
        ];

        $base64 = base64_encode(config('spotifyConfig.client_id') . ':' . config('spotifyConfig.client_secret'));

        $headers = ['Authorization' => 'Basic ' . $base64];

        for ($i = 0; $i < 3; $i++) {
            try {
                $result = $this->request('POST', $this->tokenUrl, $parameters, $headers);
                break;
            } catch (\Exception $e) {
                sleep(3);
            }
        }

        if (!isset($result)) {
            throw new \Exception('Access token not received');
        }

        $this->saveClientAccessToken($result->access_token, $result->expires_in);

        return $result->access_token;
    }

    public function getUserData($accessToken)
    {
        $headers = ['Authorization' => 'Bearer ' . $accessToken];
        return $this->request('GET', $this->apiUrl . '/me', [], $headers);
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

    public function getLastArtistSingle($accessToken, $artistId)
    {
        $parameters = [
            'include_groups' => 'single',
            'limit' => 1,
        ];

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . "/artists/{$artistId}/albums?", $parameters, $headers);
    }

    public function getArtistAlbums($accessToken, $artistId)
    {
        $parameters = [
            'include_groups' => 'album,single',
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

    public function getNewReleases($accessToken, $option = 'new', $market = 'RU', $offset = null)
    {
        $parameters = [
            'q' => 'tag:' . $option,
            'type' => 'album',
            'limit' => '50',
            'market' => $market,
        ];

        if ($offset) {
            $parameters['offset'] = $offset;
        }

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . '/search?', $parameters, $headers);
    }

    public function getMarkets($accessToken)
    {
        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . '/markets', [], $headers);
    }

    public function areRequestsAvailable()
    {
        $availableSince = Cache::get('spotify-requests-available-since', 0);
        return time() > $availableSince;
    }

    public function getNewReleases2($accessToken)
    {
        $parameters = [
            'country' => 'GB',
            'limit' => '50',
            'offset' => '99',
        ];

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . '/browse/new-releases', $parameters, $headers);
    }

    public function getAvailableGenreSeeds($accessToken)
    {
        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . '/recommendations/available-genre-seeds', [], $headers);
    }

    public function getSeveralAlbums($accessToken, array $albumIds)
    {
        $ids = implode(',', $albumIds);

        $parameters = [
            'ids' => $ids
        ];

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . '/albums', $parameters, $headers);
    }

    public function getSeveralArtists($accessToken, array $artistIds)
    {
        $ids = implode(',', $artistIds);

        $parameters = [
            'ids' => $ids
        ];

        $headers = ['Authorization' => 'Bearer ' . $accessToken];

        return $this->request('GET', $this->apiUrl . '/artists', $parameters, $headers);
    }

    private function createState()
    {
        $state = uniqid(rand(), true);
        session(['state' => $state]);
        return $state;
    }

    private function request($method, $url, $parameters = [], $headers = [])
    {
        for ($i = 1; $i <= 3; $i++) {
            $request = Http::withHeaders($headers)->asForm()->retry(3, 1000, function ($exception) {
                $statusCode = $exception->response->status();
                Log::info('HTTP Laravel retry; Status code: ' . $statusCode);
                return ($statusCode >= 500 && $statusCode <= 599);
            });

            while (true) {
                if (RateLimiter::remaining('spotify-request', config('spotifyConfig.requestRateLimitAttempts'))) {
                    RateLimiter::hit('spotify-request', config('spotifyConfig.requestRateLimitDecay'));

                    switch ($method) {
                        case 'GET':
                            $response = $request->get($url, $parameters);
                            break;
                        case 'POST':
                            $response = $request->post($url, $parameters);
                            break;
                        default:
                            throw new \Exception('Wrong method: ' . $method);
                    }

                    break;
                } else {
                    $seconds = RateLimiter::availableIn('spotify-request');
                    sleep($seconds);
                }
            }

            if ($response->successful()) {
                return $response->object();
            } else {
                if ($response->status() != 429) {
                    $status = $response->status();
                    $message = $response->json('error')['message'];
                    throw new SpotifyRequestException("{$status} - {$message}: {$method} {$url}");
                } else {
                    $retryAfter = $response->header('Retry-After') ?? 0;
                    Log::info('Spotify Request: Retry-After=' . $retryAfter . ' seconds');
                    if ($retryAfter <= 60) {
                        sleep($retryAfter + 1);
                    } else {
                        Cache::put('spotify-requests-available-since', time() + $retryAfter);
                        throw new SpotifyRequestException('Spotify Request: Retry-After time is too big');
                    }
                }
            }
        }
        throw new SpotifyRequestException('Spotify Request: Retry limit exceed');
    }

    private function saveAccessToken($accessToken, $expires_in)
    {
        session([
            'access_token' => $accessToken,
            'expiring_time' => time() + $expires_in - 10,
        ]);
    }

    private function saveClientAccessToken($accessToken, $expires_in)
    {
        try {
            Cache::put('client_access_token', $accessToken, $expires_in - 10);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'method' => __METHOD__
            ]);
        }
    }
}
