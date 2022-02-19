<?php

declare(strict_types=1);

namespace App\Services\SpotifyApi;

use App\Contracts\SpotifyApiClientInterface;
use App\Services\Spotify;

class SpotifyApiClient implements SpotifyApiClientInterface
{
    /** @var string */
    private string $authUrl;

    /** @var string */
    private string $tokenUrl;

    /** @var string */
    private string $apiUrl;

    /** @var string */
    private string $scope;

    public function __construct(
        private Spotify $oldClient = new Spotify(),//TODO temporary solution
        private Auth $auth,
        private ApiClient $apiClient,
    ) {
        $this->authUrl = config('spotifyApi.authUrl');
        $this->tokenUrl = config('spotifyApi.tokenUrl');
        $this->apiUrl = config('spotifyApi.apiUrl');
        $this->scope = config('spotifyApi.scope');
    }


    public function getAuthUrl(string $redirectUri): string
    {
        // TODO: Implement getAuthUrl() method.
    }

    public function getAccessToken(string $code)
    {
        // TODO: Implement getAccessToken() method.
    }
}
