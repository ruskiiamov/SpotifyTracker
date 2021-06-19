<?php


namespace App\Services;


use App\Models\Artist;
use App\Models\Following;
use App\Models\User;
use App\Facades\Spotify;

class Tasks
{
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
}
