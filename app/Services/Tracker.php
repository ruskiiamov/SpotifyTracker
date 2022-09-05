<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\GenreCategorizerInterface;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\User;
use App\Facades\Spotify;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use stdClass;

class Tracker
{
    /**
     * @param int $releaseAge
     * @param array $exceptions
     * @param array $artistIdExceptions
     * @param array $bannedGenreNames
     * @param GenreCategorizerInterface $genreCategorizer
     */
    public function __construct(
        private readonly int   $releaseAge,
        private readonly array $exceptions,
        private readonly array $artistIdExceptions,
        private readonly array $bannedGenreNames,
        private readonly GenreCategorizerInterface $genreCategorizer
    ) {}

    /**
     * @param User $user
     * @return void
     */
    public function updateUserFollowedArtists(User $user): void
    {
        $accessToken = $this->getUserAccessToken($user);
        $after = null;
        $actualArtistsIdList = [];

        while (true) {
            $result = Spotify::getFollowedArtists($accessToken, $after);
            $artists = $result->artists->items;

            foreach ($artists as $item) {
                try {
                    $artist = Artist::firstOrCreate(
                        ['spotify_id' => $item->id],
                        ['name' => $item->name]
                    );
                    $actualArtistsIdList[] = $artist->id;
                } catch (Exception $e) {
                    Log::error($e->getMessage(), [
                        'method' => __METHOD__,
                        'user_id' => $user->id,
                        'artist_spotify_id' => $item->id,
                    ]);
                }
            }

            if (empty($result->artists->cursors->after)) {
                break;
            } else {
                $after = $result->artists->cursors->after;
            }
        }

        $user->artists()->sync(array_unique($actualArtistsIdList));
    }

    /**
     * @param Artist $artist
     * @return void
     */
    public function addLastArtistAlbum(Artist $artist): void
    {
        $lastAlbum = $this->getLastAlbum($artist);
        $lastSingle = $this->getLastSingle($artist);

        $albumSaved = false;

        if (!empty($lastAlbum) && $this->isAlbumOk($lastAlbum)) {
            $this->saveAlbum($artist, $lastAlbum);
            $albumSaved = true;
        }

        if (!empty($lastSingle) && $this->isAlbumOk($lastSingle)) {
            $this->saveAlbum($artist, $lastSingle);
            $albumSaved = true;
        }

        if ($albumSaved) {
            $fullArtist = $this->getFullArtist($artist->spotify_id);
            if (!empty($fullArtist)) {
                $this->updateArtistGenres($artist, $fullArtist);
            }
        }

        $artist->update(['checked_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * @param array $albumIds
     * @return void
     */
    public function updateAlbums(array $albumIds): void
    {
        $accessToken = $this->getClientAccessToken();

        $albums = Album::whereIn('id', $albumIds)->get();
        $albumSpotifyIds = $albums->pluck('spotify_id')->toArray();
        $result = Spotify::getSeveralAlbums($accessToken, $albumSpotifyIds);
        $fullAlbums = $result->albums;

        foreach ($fullAlbums as $fullAlbum) {
            try {
                $album = $albums->where('spotify_id', $fullAlbum->id)->first();
                $popularity = $fullAlbum->popularity;
                $markets = json_encode($fullAlbum->available_markets, JSON_UNESCAPED_UNICODE);
                $image = $fullAlbum->images[1]->url;
                if ($popularity != $album->popularity) {
                    $album->popularity = $popularity;
                }
                if ($markets != $album->markets) {
                    $album->markets = $markets;
                }
                if ($album->image != $image) {
                    $album->image = $image;
                }
                if ($album->isDirty()) {
                    $album->save();
                }
            } catch (Exception $e) {
                Log::error($e->getMessage(), [
                    'method' => __METHOD__,
                    'album_id' => $fullAlbum->id ?? null,
                ]);
            }
        }
    }

    /**
     * @return void
     */
    public function clearArtists(): void
    {
        Artist::doesntHave('followings')->doesntHave('albums')->delete();
    }

    /**
     * @param string $searchTag
     * @param string $market
     * @return void
     */
    public function addNewReleases(string $searchTag, string $market): void
    {
        $accessToken = $this->getClientAccessToken();
        $offset = null;

        while ($offset <= 950) {
            $result = Spotify::getNewReleases($accessToken, $searchTag, $market, $offset);
            $offset = $offset + 50;
            $albums = $result->albums->items;

            foreach ($albums as $album) {
                try {
                    if (!$this->isAlbumOk($album)) {
                        continue;
                    }

                    $artistSpotifyId = $album->artists[0]->id;
                    $fullArtist = $this->getFullArtist($artistSpotifyId);
                    if (empty($fullArtist)) {
                        continue;
                    }

                    $artist = Artist::firstOrCreate(
                        ['spotify_id' => $fullArtist->id],
                        ['name' => $fullArtist->name]
                    );

                    $this->updateArtistGenres($artist, $fullArtist);

                    $this->saveAlbum($artist, $album);
                } catch (Exception $e) {
                    Log::error($e->getMessage(), [
                        'method' => __METHOD__,
                        'album_spotify_id' => $album->id
                    ]);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getCurrentMarkets(): array
    {
        $accessToken = $this->getClientAccessToken();

        try {
            $markets = Spotify::getMarkets($accessToken)->markets;
        } catch (Exception $e) {
            Log::error($e->getMessage(), [
                'method' => __METHOD__
            ]);
        }

        return $markets ?? [];
    }

    /**
     * @param Artist $artist
     * @return stdClass|null
     */
    private function getLastAlbum(Artist $artist): ?stdClass
    {
        $accessToken = $this->getClientAccessToken();

        $result = Spotify::getLastArtistAlbum($accessToken, $artist->spotify_id);
        $counter = 0;
        while ($result === null && $counter < 2) {
            sleep(1);
            $result = Spotify::getLastArtistAlbum($accessToken, $artist->spotify_id);
            $counter++;
        }
        return $result->items[0] ?? null;
    }

    /**
     * @param Artist $artist
     * @return stdClass|null
     */
    private function getLastSingle(Artist $artist): ?stdClass
    {
        $accessToken = $this->getClientAccessToken();

        $result = Spotify::getLastArtistSingle($accessToken, $artist->spotify_id);
        $counter = 0;
        while ($result === null && $counter < 2) {
            sleep(1);
            $result = Spotify::getLastArtistSingle($accessToken, $artist->spotify_id);
            $counter++;
        }
        return $result->items[0] ?? null;
    }

    /**
     * @param Artist $artist
     * @param stdClass $album
     * @return void
     */
    private function saveAlbum(Artist $artist, stdClass $album): void
    {
        $accessToken = $this->getClientAccessToken();

        $fullAlbum = Spotify::getAlbum($accessToken, $album->id);
        Album::create([
            'spotify_id' => $album->id,
            'name' => $fullAlbum->name,
            'release_date' => $fullAlbum->release_date,
            'artist_id' => $artist->id,
            'markets' => json_encode($fullAlbum->available_markets, JSON_UNESCAPED_UNICODE),
            'image' => $fullAlbum->images[1]->url,
            'popularity' => $fullAlbum->popularity,
            'type' => $album->album_type,
        ]);
    }

    /**
     * @param Artist $artist
     * @param stdClass $fullArtist
     * @return void
     */
    private function updateArtistGenres(Artist $artist, stdClass $fullArtist): void
    {
        $actualGenresIdList = [];
        foreach ($fullArtist->genres as $genreName) {
            if (in_array($genreName, $this->bannedGenreNames)) {
                continue;
            }

            $genre = Genre::firstOrCreate(['name' => $genreName]);
            $actualGenresIdList[] = $genre->id;

            if ($genre->categories()->doesntExist()) {
                $this->genreCategorizer->categorize($genre);
            }
        }

        $artist->genres()->sync(array_unique($actualGenresIdList));
    }

    /**
     * @param string $artistSpotifyId
     * @return stdClass|null
     */
    private function getFullArtist(string $artistSpotifyId): ?stdClass
    {
        $accessToken = $this->getClientAccessToken();

        $fullArtist = Spotify::getArtist($accessToken, $artistSpotifyId);
        if (empty($fullArtist->genres) || !$this->areGenresOk($fullArtist->genres)) {
            return null;
        }
        return $fullArtist;
    }

    /**
     * @param $lastAlbum
     * @return bool
     */
    private function isReleaseDateOk($lastAlbum): bool
    {
        $releaseDateThreshold = $this->getReleaseDateThreshold();
        return ($lastAlbum->release_date_precision === 'day' && $lastAlbum->release_date > $releaseDateThreshold);
    }

    /**
     * @return string
     */
    private function getReleaseDateThreshold(): string
    {
        return date('Y-m-d', time() - $this->releaseAge * 24 * 60 * 60);
    }

    /**
     * @param string $albumName
     * @return bool
     */
    private function isAlbumNameOk(string $albumName): bool
    {
        foreach ($this->exceptions as $exception) {
            if (str_contains(strtolower($albumName), $exception)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param User $user
     * @return string
     */
    private function getUserAccessToken(User $user): string
    {
        $refreshToken = $user->refresh_token;
        return Spotify::getRefreshedAccessToken($refreshToken);
    }

    /**
     * @return string
     */
    private function getClientAccessToken(): string
    {
        if (Cache::has('client_access_token')) {
            return Cache::get('client_access_token');
        }

        return Spotify::getClientAccessToken();
    }

    /**
     * @param array $genres
     * @return bool
     */
    private function areGenresOk(array $genres): bool
    {
        foreach ($genres as $genre) {
            if (!in_array($genre, $this->bannedGenreNames)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param stdClass $album
     * @return bool
     */
    private function isAlbumOk(stdClass $album): bool
    {
        $artistSpotifyId = $album->artists[0]->id;

        if (!in_array($album->album_type, ['album', 'single'])
            || !$this->isReleaseDateOk($album)
            || !$this->isAlbumNameOk($album->name)
            || in_array($artistSpotifyId, $this->artistIdExceptions)
            || Album::where('spotify_id', $album->id)->exists()
        ) {
            return false;
        }

        return true;
    }
}
