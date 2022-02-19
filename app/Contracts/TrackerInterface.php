<?php

declare(strict_types=1);

namespace App\Contracts;

interface TrackerInterface
{
    /**
     * @return void
     */
    public function updateFollowedArtists(): void;

    /**
     * @return void
     */
    public function addFollowedArtistsNewAlbums(): void;

    /**
     * @return void
     */
    public function addNewAlbums(): void;

    /**
     * @return void
     */
    public function updateAlbums(): void;

    /**
     * @return void
     */
    public function clearArtists(): void;
}
