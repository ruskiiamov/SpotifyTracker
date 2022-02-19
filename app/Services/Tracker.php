<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SpotifyApiClientInterface;
use App\Contracts\TrackerInterface;

class Tracker implements TrackerInterface
{
    public function __construct(
        private SpotifyApiClientInterface $spotifyApiClient
    ) {}

    public function updateFollowedArtists(): void
    {
        // TODO: Implement updateFollowedArtists() method.
    }

    public function addFollowedArtistsNewAlbums(): void
    {
        // TODO: Implement addFollowedArtistsNewAlbums() method.
    }

    public function addNewAlbums(): void
    {
        // TODO: Implement addNewAlbums() method.
    }

    public function updateAlbums(): void
    {
        // TODO: Implement updateAlbums() method.
    }

    public function clearArtists(): void
    {
        // TODO: Implement clearArtists() method.
    }
}
