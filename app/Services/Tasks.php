<?php


namespace App\Services;


use App\Models\Album;
use App\Models\Artist;
use App\Models\Connection;
use App\Models\Following;
use App\Models\Genre;
use App\Models\User;
use App\Facades\Spotify;

class Tasks
{
    private $releaseAge = 14; //days

    public function updateFollowedArtists()
    {
        $users = User::all();

        foreach ($users as $user) {
            $refreshToken = $user->refresh_token;
            $accessToken = Spotify::getRefreshedAccessToken($refreshToken);

            $after = null;
            $actualArtistsIdList = [];
            do {
                $result = Spotify::getFollowedArtists($accessToken, $after);
                $artists = $result->artists->items;
                foreach ($artists as $item) {
                    $artistId = $item->id;
                    $actualArtistsIdList[] = $artistId;
                    $artist = Artist::firstOrCreate(
                        ['spotify_id' => $artistId],
                        ['name' => $item->name]
                    );
                    $this->updateConnections($artist, $item->genres);
                    Following::firstOrCreate([
                        'user_id' => $user->id,
                        'artist_id' => $artist->id,
                    ]);
                }
                $after = $result->artists->cursors->after;
            } while ($after);

            $followings = $user->followings;
            foreach ($followings as $following) {
                if (!in_array($following->artist->spotify_id, $actualArtistsIdList)) {
                    $following->delete();
                }
            }
        }
    }

    public function updateAlbumList()
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
                $albums = $result->items;
                foreach ($albums as $album) {
                    if ($album->release_date_precision == 'day' && $album->release_date >= $releaseDateThreshold) {
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
        foreach ($genres as $genre) {
            $genre = Genre::firstOrCreate(['name' => $genre]);
            Connection::firstOrCreate([
                'artist_id' => $artist->id,
                'genre_id' => $genre->id,
            ]);
        }

        $connections = $artist->connections;
        foreach ($connections as $connection) {
            if (!in_array($connection->genre->name, $genres)) {
                $connection->delete();
            }
        }
    }

    private function getReleaseDateThreshold()
    {
        return date('Y-m-d', time() - $this->releaseAge * 24 * 60 * 60);
    }

}
