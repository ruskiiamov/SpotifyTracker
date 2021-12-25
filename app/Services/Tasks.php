<?php


namespace App\Services;


use App\Models\Album;
use App\Models\Artist;
use App\Models\Category;
use App\Models\Connection;
use App\Models\Genre;
use App\Models\User;
use App\Facades\Spotify;
use Exception;
use Illuminate\Support\Facades\Config;
use stdClass;

class Tasks
{
    private $releaseAge;
    private $checkAge;
    private $genreCategories;
    private $exceptions;
    private $artistIdExceptions;
    private $markets;

    public function __construct()
    {
        $this->releaseAge = Config::get('spotifyConfig.releaseAge');
        $this->checkAge = Config::get('spotifyConfig.checkAge');
        $this->genreCategories = Config::get('spotifyConfig.genreCategories');
        $this->exceptions = Config::get('spotifyConfig.exceptions');
        $this->artistIdExceptions = Config::get('spotifyConfig.artistIdExceptions');
        $this->markets = Config::get('spotifyConfig.markets');
    }

    /**
     * @return Report
     */
    public function updateFollowedArtists(): Report
    {
        $report = new Report(
            'analysed_artists',
            'analysed_users',
            'created_artists',
            'created_followings',
            'deleted_followings'
        );

        User::chunk(200, function ($users) use (&$report){
            foreach ($users as $user) {
                try {
                    $report->analysed_users();
                    $accessToken = $this->getUserAccessToken($user);
                    $after = null;
                    $actualArtistsIdList = [];
                    do {
                        $result = Spotify::getFollowedArtists($accessToken, $after);
                        $artists = $result->artists->items;
                        $after = $result->artists->cursors->after;
                        foreach ($artists as $item) {
                            $report->analysed_artists();
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
                                    $report->created_artists();
                                }
                                if ($user->artists()->where('artist_id', $artist->id)->doesntExist()) {
                                    $user->artists()->attach($artist->id);
                                    $report->created_followings();
                                }
                            } catch (Exception $e) {
                                $report->setErrorMessage($item->name . ': ' . $e->getMessage());
                                continue;
                            }
                        }
                    } while ($after);
                    $userArtists = $user->artists;
                    foreach ($userArtists as $userArtist) {
                        try {
                            if (!in_array($userArtist->spotify_id, $actualArtistsIdList)) {
                                $user->artists()->detach($userArtist->id);
                                $report->deleted_followings();
                            }
                        } catch (Exception $e) {
                            $report->setErrorMessage($user->email . ': ' . $e->getMessage());
                            continue;
                        }
                    }
                } catch (Exception $e) {
                    $report->setErrorMessage($user->email . ': ' . $e->getMessage());
                    continue;
                }
            }
        });
        return $report;
    }

    /**
     * @return Report
     */
    public function addFollowedAlbums(): Report
    {
        $report = new Report('analysed_artists', 'added_albums');
        $accessToken = $this->getAccessToken();
        $checkThreshold = $this->getCheckDateTimeThreshold();

        Artist::has('followings')->where('checked_at', '<', $checkThreshold)
            ->chunkById(200, function ($artists) use ($accessToken, &$report) {
                foreach ($artists as $artist) {
                    $report->analysed_artists();
                    try {
                        $lastAlbum = $this->getLastAlbum($accessToken, $artist->spotify_id);

                        $artist->checked_at = date('Y-m-d H:i:s');
                        $artist->save();

                        if (!$this->isReleaseDateOk($lastAlbum) || !$this->isAlbumNameOk($lastAlbum->name)) {
                            continue;
                        }

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
                            $report->added_albums();

                            $fullArtist = Spotify::getArtist($accessToken, $artist->spotify_id);
                            $this->updateConnections($artist, $fullArtist->genres);
                        }
                    } catch (Exception $e) {
                        $report->setErrorMessage($artist->name . ': ' . $e->getMessage());
                        continue;
                    }
                }
            });
        return $report;
    }

    /**
     * @return Report
     */
    public function updateAlbums(): Report
    {
        $report = new Report('deleted_albums', 'updated_albums');
        $accessToken = $this->getAccessToken();
        Album::chunk(200, function ($albums) use ($accessToken, &$report) {
            $releaseDateThreshold = $this->getReleaseDateThreshold();
            foreach ($albums as $album) {
                try {
                    if ($album->release_date < $releaseDateThreshold) {
                        $album->delete();
                        $report->deleted_albums();
                    } else {
                        $albumSpotifyId = $album->spotify_id;
                        $fullAlbum = Spotify::getAlbum($accessToken, $albumSpotifyId);
                        $popularity = $fullAlbum->popularity;
                        if ($popularity != $album->popularity) {
                            $album->popularity = $popularity;
                            $album->save();
                            $report->updated_albums();
                        }
                    }
                } catch (Exception $e) {
                    $report->setErrorMessage('id=' . $album->id . ' ' . $album->name . ': ' . $e->getMessage());
                    continue;
                }
            }
        });
        return $report;
    }

    /**
     * @return Report
     */
    public function clearArtists(): Report
    {
        $report = new Report('deleted_artists');
        try {
            $deletedArtists = Artist::doesntHave('followings')->doesntHave('albums')->delete();
            $report->setValue('deleted_artists', $deletedArtists);
        } catch (Exception $e) {
            $report->setErrorMessage($e->getMessage());
        }
        return $report;
    }

    /**
     * @return Report
     */
    public function addNewReleases(): Report
    {
        $report = new Report('analysed_albums', 'created_artists', 'added_albums');

        foreach ($this->markets as $market) {
            $report = $this->addReleases('new', $market, $report);
        }

        foreach ($this->markets as $market) {
            $report = $this->addReleases('hipster', $market, $report);
        }

        return $report;
    }

    /**
     * @param string $mode
     * @param string $market
     * @param Report $report
     * @return Report
     */
    private function addReleases(string $mode, string $market, Report $report): Report
    {
        $accessToken = $this->getAccessToken();

        $offset = null;
        do {
            $result = Spotify::getNewReleases($accessToken, $mode, $market, $offset);
            $offset = $offset + 50;
            try {
                $albums = $result->albums->items;
            } catch (Exception $e) {
                $report->setErrorMessage($e->getMessage());
                continue;
            }
            foreach ($albums as $album) {
                try {
                    $report->analysed_albums();

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

                    $artist = Artist::where('spotify_id', $fullArtist->id)->first();
                    if (!isset($artist)) {
                        $artist = new Artist();
                        $artist->fill([
                            'spotify_id' => $fullArtist->id,
                            'name' => $fullArtist->name,
                        ])->save();
                        $report->created_artists();
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
                    $report->added_albums();
                } catch (Exception $e) {
                    $report->setErrorMessage('id=' . ($album->id ?? '?') . ' ' . ($album->name ?? '?') . ': ' . $e->getMessage());
                    continue;
                }
            }
        } while ($offset <= 950);
        return $report;
    }

    /**
     * @return array
     */
    public function genresAnalyse(): array
    {
        $words = [];
        $genres = Genre::all();
        foreach ($genres as $genre) {
            $separated = explode(' ', strtolower($genre->name));
            foreach ($separated as $item) {
                if (array_key_exists($item, $words)) {
                    $words[$item]++;
                } else {
                    $words[$item] = 1;
                }
            }
        }
        arsort($words);
        $result[] = [];
        foreach ($words as $word => $amount) {
            $result[] = [$word, $amount];
        }
        return $result;
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
            sleep(0.2);
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
    private function updateConnections(Artist $artist, array $genreNames): void
    {
        $artistGenres = $artist->genres;
        foreach ($artistGenres as $artistGenre) {
            if (!in_array($artistGenre->name, $genreNames)) {
                $artist->genres()->detach($artistGenre->id);
            }
        }

        foreach ($genreNames as $genreName) {
            $genre = Genre::firstOrCreate(
                ['name' => $genreName],
                ['category_id' => Category::where('name', $this->setGenreCategory($genreName))->first()->id],
            );

            if ($artist->genres()->where('name', $genre)->doesntExist()) {
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
     * @return string
     */
    private function getCheckDateTimeThreshold(): string
    {
        return date('Y-m-d H:i:s', time() - $this->checkAge * 60 * 60);
    }

    /**
     * @param string $genre
     * @return string
     */
    private function setGenreCategory(string $genre): string
    {
        foreach ($this->genreCategories as $genreCategory => $keyWords) {
            foreach ($keyWords as $keyWord) {
                if (str_contains(strtolower($genre), $keyWord)) {
                    return $genreCategory;
                }
            }
        }
        return 'other';
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
        $accessToken = Spotify::getRefreshedAccessToken($refreshToken);
        return $accessToken;
    }

    /**
     * @return string
     */
    private function getAccessToken(): string
    {
        $refreshToken = User::first()->refresh_token;
        return Spotify::getRefreshedAccessToken($refreshToken);
    }

}
