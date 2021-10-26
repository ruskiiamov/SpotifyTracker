<?php


namespace App\Services;


use App\Models\Album;
use App\Models\Artist;
use App\Models\Category;
use App\Models\Connection;
use App\Models\Following;
use App\Models\Genre;
use App\Models\User;
use App\Facades\Spotify;
use Illuminate\Support\Facades\Config;
use stdClass;

class Tasks
{
    private $releaseAge;
    private $checkAge;
    private $genreCategories;
    private $exceptions;
    private $artistIdExceptions;

    public function __construct()
    {
        $this->releaseAge = Config::get('spotifyConfig.releaseAge');
        $this->checkAge = Config::get('spotifyConfig.checkAge');
        $this->genreCategories = Config::get('spotifyConfig.genreCategories');
        $this->exceptions = Config::get('spotifyConfig.exceptions');
        $this->artistIdExceptions = Config::get('spotifyConfig.artistIdExceptions');
    }

    /**
     * Update followed artists for all users
     *
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
                                $following = Following::where('user_id', $user->id)
                                    ->where('artist_id', $artist->id)->first();
                                if (!isset($following)) {
                                    $following = new Following();
                                    $following->fill([
                                        'user_id' => $user->id,
                                        'artist_id' => $artist->id,
                                    ])->save();
                                    $report->created_followings();
                                }
                            } catch (\Exception $e) {
                                $report->setErrorMessage($item->name . ': ' . $e->getMessage());
                                continue;
                            }
                        }
                    } while ($after);
                    $followings = $user->followings;
                    foreach ($followings as $following) {
                        try {
                            if (!in_array($following->artist->spotify_id, $actualArtistsIdList)) {
                                $following->delete();
                                $report->deleted_followings();
                            }
                        } catch (\Exception $e) {
                            $report->setErrorMessage($user->email . ': ' . $e->getMessage());
                            continue;
                        }
                    }
                } catch (\Exception $e) {
                    $report->setErrorMessage($user->email . ': ' . $e->getMessage());
                    continue;
                }
            }
        });
        return $report;
    }

    /**
     * Add new albums for followed artists
     *
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

                        $newAlbum = Album::where('spotify_id', $lastAlbum->id)->first();
                        if (!isset($newAlbum)) {
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
                    } catch (\Exception $e) {
                        $report->setErrorMessage($artist->name . ': ' . $e->getMessage());
                        continue;
                    }
                }
            });
        return $report;
    }

    /**
     * Update albums popularity and delete obsolete albums
     *
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
                } catch (\Exception $e) {
                    $report->setErrorMessage('id=' . $album->id . ' ' . $album->name . ': ' . $e->getMessage());
                    continue;
                }
            }
        });
        return $report;
    }

    /**
     * Get artist last album (3 attempts)
     *
     * @param string $accessToken
     * @param string $spotifyId
     * @return stdClass
     */
    private function getLastAlbum(string $accessToken,string $spotifyId): stdClass
    {
        $result = Spotify::getLastArtistAlbum($accessToken, $spotifyId);
        $counter = 0;
        while ($result === null && $counter < 2) {
            sleep(0.3);
            $result = Spotify::getLastArtistAlbum($accessToken, $spotifyId);
            $counter++;
        }
        return $result->items[0];
    }


    private function isReleaseDateOk($lastAlbum)
    {
        $releaseDateThreshold = $this->getReleaseDateThreshold();
        return ($lastAlbum->release_date_precision === 'day' && $lastAlbum->release_date > $releaseDateThreshold);
    }

    private function updateConnections(Artist $artist, $genres)
    {
        $this->addGenres($genres);
        foreach ($genres as $genre) {
            Connection::firstOrCreate([
                'artist_id' => $artist->id,
                'genre_id' => Genre::where('name', $genre)->first()->id,
            ]);
        }

        $connections = $artist->connections;
        foreach ($connections as $connection) {
            if (!in_array($connection->genre->name, $genres)) {
                $connection->delete();
            }
        }
    }

    private function addGenres($genres) //TODO shift that code to updateConnections method
    {
        foreach ($genres as $genre) {
            Genre::firstOrCreate(
                ['name' => $genre],
                ['category_id' => Category::where('name', $this->setGenreCategory($genre))->first()->id],
            );
        }
    }

    private function getReleaseDateThreshold()
    {
        return date('Y-m-d', time() - $this->releaseAge * 24 * 60 * 60);
    }

    private function getCheckDateTimeThreshold()
    {
        return date('Y-m-d H:i:s', time() - $this->checkAge * 60 * 60);
    }

    public function genresAnalyse()
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

    private function setGenreCategory($genre)
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

    public function clearArtists()
    {
        Artist::chunk(200, function ($artists) {
            foreach ($artists as $artist) {
                $following = $artist->followings->first();
                $album = $artist->albums->first();
                if (is_null($following) && is_null($album)) {
                    $connections = $artist->connections;
                    foreach ($connections as $connection) {
                        $connection->delete();
                    }
                    $artist->delete();
                }
            }
        });
    }

    /**
     *
     * Check album name for banned words: live, deluxe, etc
     *
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

    public function addNewReleases()
    {
        $refreshToken = User::first()->refresh_token;
        $accessToken = Spotify::getRefreshedAccessToken($refreshToken);

        $offset = null;
        do {
            $result = Spotify::getNewReleases($accessToken, $offset);
            $offset = $offset + 50;
            $albums = $result->albums->items;
            foreach ($albums as $album) {
                if ($album->release_date_precision == 'day' && $album->album_type == 'album' && $this->isAlbumNameOk($album->name)) {
                    $artistSpotifyId = $album->artists[0]->id;
                    if (in_array($artistSpotifyId, $this->artistIdExceptions)) {
                        continue;
                    }
                    $fullArtist = Spotify::getArtist($accessToken, $artistSpotifyId);
                    try {
                        $this->addGenres($fullArtist->genres); //TODO remove that
                    } catch (\Throwable $e) {
                        continue;
                    }
                    if (!$this->isGenreSubscribed($fullArtist->genres)) {
                        continue;
                    }

                    $artist = Artist::firstOrCreate(
                        ['spotify_id' => $fullArtist->id],
                        ['name' => $fullArtist->name]
                    );
                    $this->updateConnections($artist, $fullArtist->genres);

                    $albumSpotifyId = $album->id;
                    $fullAlbum = Spotify::getAlbum($accessToken, $albumSpotifyId);

                    try {
                        Album::firstOrCreate(
                            ['spotify_id' => $albumSpotifyId],
                            [
                                'name' => $fullAlbum->name,
                                'release_date' => $fullAlbum->release_date,
                                'artist_id' => $artist->id,
                                'markets' => json_encode($fullAlbum->available_markets, JSON_UNESCAPED_UNICODE),
                                'image' => $fullAlbum->images[1]->url,
                                'popularity' => $fullAlbum->popularity,
                            ]
                        );
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
            }
        } while ($offset <= 950);
    }

    private function isGenreSubscribed($genreNames)
    {
        foreach ($genreNames as $genreName) {
            $genre = Genre::where('name', $genreName)->first();
            $category = $genre->category;
            $subscription = $category->subscriptions->first();
            if (!is_null($subscription)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get access token for specified user
     *
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
     * Get access token for random user
     *
     * @return string
     */
    private function getAccessToken(): string
    {
        $refreshToken = User::first()->refresh_token;
        return Spotify::getRefreshedAccessToken($refreshToken);
    }

}
