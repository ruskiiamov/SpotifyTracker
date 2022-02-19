<?php

declare(strict_types=1);

namespace App\Contracts;

interface SpotifyApiClientInterface
{
    /**
     * @param string $redirectUri
     * @return string
     */
    public function getAuthUrl(string $redirectUri): string;

    public function getAccessToken(string $code);
}
