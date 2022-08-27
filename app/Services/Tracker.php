<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Category;
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
     * @param array $genreCategories
     * @param array $exceptions
     * @param array $artistIdExceptions
     * @param array $bannedGenreNames
     */
    public function __construct(
        private int $releaseAge,
        private array $genreCategories,
        private array $exceptions,
        private array $artistIdExceptions,
        private array $bannedGenreNames
    ) {}

    /**
     * @param User $user
     * @return void
     */
    public function updateUserFollowedArtists(User $user)
    {
        $accessToken = $this->getUserAccessToken($user);
        $after = null;
        $actualArtistsIdList = [];

        do {
            $result = Spotify::getFollowedArtists($accessToken, $after);
            $artists = $result->artists->items;
            $after = $result->artists->cursors->after;

            foreach ($artists as $item) {
                try {
                    $artistId = $item->id;
                    $actualArtistsIdList[] = $artistId;
                    $artist = Artist::where('spotify_id', $artistId)->first();

                    if (!isset($artist)) {
                        $artist = new Artist();
                        $artist->fill([
                            'spotify_id' => $artistId,
                            'name' => $item->name,
                        ])->save();
                    }

                    if ($user->artists()->where('artist_id', $artist->id)->doesntExist()) {
                        $user->artists()->attach($artist->id);
                    }
                } catch (Exception $e) {
                    Log::error($e->getMessage(), [
                        'method' => __METHOD__,
                        'user_id' => $user->id,
                        'artist_spotify_id' => $artistId,
                    ]);
                }
            }
        } while ($after);

        $userArtists = $user->artists;

        foreach ($userArtists as $userArtist) {
            try {
                if (!in_array($userArtist->spotify_id, $actualArtistsIdList)) {
                    $user->artists()->detach($userArtist->id);
                }
            } catch (Exception $e) {
                Log::error($e->getMessage(), [
                    'method' => __METHOD__,
                    'user_id' => $user->id,
                    'artist_id' => $userArtist->id,
                ]);
            }
        }
    }

    /**
     * @param Artist $artist
     * @return void
     */
    public function addLastArtistAlbum(Artist $artist)
    {
        $accessToken = $this->getClientAccessToken();

        $lastAlbum = $this->getLastAlbum($accessToken, $artist->spotify_id);

        $artist->checked_at = date('Y-m-d H:i:s');
        $artist->save();

        if ($this->isReleaseDateOk($lastAlbum) && $this->isAlbumNameOk($lastAlbum->name)) {
            if (Album::where('spotify_id', $lastAlbum->id)->doesntExist()) {
                $fullAlbum = Spotify::getAlbum($accessToken, $lastAlbum->id);
                $newAlbum = new Album();
                $newAlbum->fill([
                    'spotify_id' => $lastAlbum->id,
                    'name' => $fullAlbum->name,
                    'release_date' => $fullAlbum->release_date,
                    'artist_id' => $artist->id,
                    'markets' => json_encode($fullAlbum->available_markets, JSON_UNESCAPED_UNICODE),
                    'image' => $fullAlbum->images[1]->url,
                    'popularity' => $fullAlbum->popularity,
                ])->save();

                $fullArtist = Spotify::getArtist($accessToken, $artist->spotify_id);
                $this->updateConnections($artist, $fullArtist->genres);
            }
        }
    }

    /**
     * @param Album $album
     * @return void
     */
    public function updateAlbum(Album $album)
    {
        $accessToken = $this->getClientAccessToken();
        $releaseDateThreshold = $this->getReleaseDateThreshold();

        if ($album->release_date < $releaseDateThreshold) {
            $album->delete();
        } else {
            $albumSpotifyId = $album->spotify_id;
            $fullAlbum = Spotify::getAlbum($accessToken, $albumSpotifyId);
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
            if ($album->wasChanged()) {
                $album->save();
            }
        }
    }

    /**
     * @return void
     */
    public function clearArtists()
    {
        Artist::doesntHave('followings')->doesntHave('albums')->delete();
    }

    /**
     * @param string $searchTag
     * @param string $market
     * @return void
     */
    public function addNewReleases(string $searchTag, string $market)
    {
        $accessToken = $this->getClientAccessToken();
        $offset = null;

        do {
            $result = Spotify::getNewReleases($accessToken, $searchTag, $market, $offset);
            $offset = $offset + 50;
            $albums = $result->albums->items;

            foreach ($albums as $album) {
                try {
                    if ($album->album_type !== 'album') {
                        continue;
                    }

                    if (!$this->isReleaseDateOk($album) || !$this->isAlbumNameOk($album->name)) {
                        continue;
                    }

                    $artistSpotifyId = $album->artists[0]->id;

                    if (in_array($artistSpotifyId, $this->artistIdExceptions)) {
                        continue;
                    }

                    if (Album::where('spotify_id', $album->id)->exists()) {
                        continue;
                    }

                    $fullArtist = Spotify::getArtist($accessToken, $artistSpotifyId);
                    if (empty($fullArtist->genres) || !$this->areGenresOk($fullArtist->genres)) {
                        continue;
                    }

                    $artist = Artist::where('spotify_id', $fullArtist->id)->first();

                    if (!isset($artist)) {
                        $artist = new Artist();
                        $artist->fill([
                            'spotify_id' => $fullArtist->id,
                            'name' => $fullArtist->name,
                        ])->save();
                    }

                    $this->updateConnections($artist, $fullArtist->genres);

                    $albumSpotifyId = $album->id;
                    $fullAlbum = Spotify::getAlbum($accessToken, $albumSpotifyId);

                    $newAlbum = new Album();
                    $newAlbum->fill([
                        'spotify_id' => $albumSpotifyId,
                        'name' => $fullAlbum->name,
                        'release_date' => $fullAlbum->release_date,
                        'artist_id' => $artist->id,
                        'markets' => json_encode($fullAlbum->available_markets, JSON_UNESCAPED_UNICODE),
                        'image' => $fullAlbum->images[1]->url,
                        'popularity' => $fullAlbum->popularity,
                    ])->save();
                } catch (Exception $e) {
                    Log::error($e->getMessage(), [
                        'method' => __METHOD__,
                        'album_spotify_id' => $album->id
                    ]);
                }
            }
        } while ($offset <= 950);
    }

    /**
     * @return array
     */
    public function getCurrentMarkets(): array
    {
        $accessToken = $this->getClientAccessToken();

        try {
            $markets = Spotify::getMarkets($accessToken)->markets;
        } catch (Exception $e) {}

        return $markets ?? [];
    }

    /**
     * @param string $accessToken
     * @param string $spotifyId
     * @return stdClass
     */
    private function getLastAlbum(string $accessToken,string $spotifyId): stdClass
    {
        $result = Spotify::getLastArtistAlbum($accessToken, $spotifyId);
        $counter = 0;
        while ($result === null && $counter < 2) {
            sleep(1);
            $result = Spotify::getLastArtistAlbum($accessToken, $spotifyId);
            $counter++;
        }
        return $result->items[0];
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
     * @param Artist $artist
     * @param array $genreNames
     */
    private function updateConnections(Artist $artist, array $genreNames)
    {
        $artistGenres = $artist->genres;
        foreach ($artistGenres as $artistGenre) {
            if (!in_array($artistGenre->name, $genreNames)) {
                $artist->genres()->detach($artistGenre->id);
            }
        }

        foreach ($genreNames as $genreName) {
            if (in_array($genreName, $this->bannedGenreNames)) {
                continue;
            }
            $genre = Genre::firstOrCreate(
                ['name' => $genreName],
                ['category_id' => Category::where('name', $this->getGenreCategory($genreName))->first()->id],
            );

            if ($artist->genres()->where('name', $genreName)->doesntExist()) {
                $artist->genres()->attach($genre->id);
            }
        }
    }

    /**
     * @return string
     */
    private function getReleaseDateThreshold(): string
    {
        return date('Y-m-d', time() - $this->releaseAge * 24 * 60 * 60);
    }

    /**
     * @param string $genre
     * @return string
     */
    public function getGenreCategory(string $genre): string
    {
        foreach ($this->genreCategories as $genreCategory => $keyWords) {
            foreach ($keyWords as $keyWord) {
                if (str_contains(strtolower($genre), $keyWord)) {
                    return $genreCategory;
                }
            }
        }
        return 'Other';
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
}
