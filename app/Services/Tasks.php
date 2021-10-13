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

class Tasks
{
    private $releaseAge;
    private $genreCategories;
    private $exceptions;
    private $artistIdExceptions;

    public function __construct()
    {
        $this->releaseAge = Config::get('spotifyConfig.releaseAge');
        $this->genreCategories = Config::get('spotifyConfig.genreCategories');
        $this->exceptions = Config::get('spotifyConfig.exceptions');
        $this->artistIdExceptions = Config::get('spotifyConfig.artistIdExceptions');
    }

    /**
     * Update followed artists for all users
     *
     * @return int[]
     */
    public function updateFollowedArtists(): array
    {
        $report = [
            'analysed_artists' => 0,
            'analysed_users' => 0,
            'created_artists' => 0,
            'created_followings' => 0,
            'deleted_followings' => 0,
            'error_messages' => [],
        ];
//        $artistAmount = 0;
//        $userAmount = 0;
//        $deletedAmount = 0;
//        $addedAmount = 0;
//        $createdAmount = 0;
//        $errors = [];

        User::chunk(200, function ($users) use (&$report){
            foreach ($users as $user) {
                $report['analysed_users']++;

                try {
                    $refreshToken = $user->refresh_token;
                    $accessToken = Spotify::getRefreshedAccessToken($refreshToken);
                } catch (\Exception $e) {
                    $report['error_messages'][] = $e->getMessage();
                    continue;
                }

                $after = null;
                $actualArtistsIdList = [];
                do {
                    try {
                        $result = Spotify::getFollowedArtists($accessToken, $after);
                        $artists = $result->artists->items;
                        $after = $result->artists->cursors->after;
                    } catch (\Exception $e) {
                        $report['error_messages'][] = $e->getMessage();
                        break;
                    }
                    foreach ($artists as $item) {
                        $report['analysed_artists']++;
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
                                $report['created_artists']++;
                                $this->updateConnections($artist, $item->genres); //TODO create separated command for all artists (now artists can be repeated)
                            }

//                            $artist = Artist::firstOrCreate(
//                                ['spotify_id' => $artistId],
//                                ['name' => $item->name]
//                            );

                            Following::firstOrCreate([ //TODO add counter for created followings
                                'user_id' => $user->id,
                                'artist_id' => $artist->id,
                            ]);
                        } catch (\Exception $e) {
                            $report['error_messages'][] = $e->getMessage();
                            continue;
                        }
                    }
                } while ($after);

                try {
                    $followings = $user->followings;
                } catch (\Exception $e) {
                    $report['error_messages'][] = $e->getMessage();
                    continue;
                }
                foreach ($followings as $following) {
                    try {
                        if (!in_array($following->artist->spotify_id, $actualArtistsIdList)) {
                            $following->delete();
                            $report['deleted_followings']++;
                        }
                    } catch (\Exception $e) {
                        $report['error_messages'][] = $e->getMessage();
                        continue;
                    }
                }
            }
        });
        return $report;
    }

    public function addFollowedAlbums()
    {
        $refreshToken = User::first()->refresh_token;
        $accessToken = Spotify::getRefreshedAccessToken($refreshToken);

        Artist::chunk(200, function ($artists) use ($accessToken) {
            $releaseDateThreshold = $this->getReleaseDateThreshold();
            foreach ($artists as $artist) {
                if (is_null($artist->followings->first())) {
                    continue;
                }
                $result = Spotify::getArtistAlbums($accessToken, $artist->spotify_id);
                try {
                    $albums = $result->items;
                } catch (\Throwable $e) {
                    continue;
                }
                foreach ($albums as $album) {
                    if ($album->release_date_precision == 'day' && $album->release_date >= $releaseDateThreshold) {
                        $flag = false;
                        foreach ($this->exceptions as $exception) {
                            if (str_contains(strtolower($album->name), $exception)) {
                                $flag = true;
                                break;
                            }
                        }
                        if ($flag) {continue;}
                        $albumSpotifyId = $album->id;
                        $fullAlbum = Spotify::getAlbum($accessToken, $albumSpotifyId);
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
                    }
                }
            }
        });
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

    private function addGenres($genres)
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

    public function clearAlbums()
    {
        $refreshToken = User::first()->refresh_token;
        $accessToken = Spotify::getRefreshedAccessToken($refreshToken);

        Album::chunk(200, function ($albums) use ($accessToken) {
            $releaseDateThreshold = $this->getReleaseDateThreshold();
            foreach ($albums as $album) {
                if ($album->release_date < $releaseDateThreshold) {
                    $album->delete();
                } else {
                    $albumSpotifyId = $album->spotify_id;
                    $fullAlbum = Spotify::getAlbum($accessToken, $albumSpotifyId);
                    $popularity = $fullAlbum->popularity;
                    if ($popularity != $album->popularity) {
                        $album->popularity = $popularity;
                        $album->save();
                    }
                }
            }
        });
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

    private function isAlbumNameOk($albumName)
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
                        $this->addGenres($fullArtist->genres);
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

}
