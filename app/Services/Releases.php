<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\WarmUpAlbumCache;
use App\Jobs\WarmUpAlbumsCache;
use App\Jobs\WarmUpFollowedAlbumsCache;
use App\Jobs\WarmUpNewReleaseAlbumsCache;
use App\Models\Album;
use App\Models\Connection;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class Releases
{
    /**
     * @param User $user
     * @param int $onlyAlbums
     * @param int $page
     * @return PaginatorContract
     */
    public function getFollowedAlbumsPaginator(User $user, int $onlyAlbums, int $page): PaginatorContract
    {
        $followedCacheKey = "followed={$user->id}::only_albums={$onlyAlbums}";
        if (Cache::has($followedCacheKey)) {
            $paginator = $this->getCachedAlbumsPaginator(
                cacheKey: $followedCacheKey,
                page: $page,
                rout: route('followed')
            );
        } else {
            WarmUpFollowedAlbumsCache::dispatch($user)->onQueue('high');

            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });

            $paginator = $this->getFollowedAlbumsQueryBuilder(
                user: $user,
                onlyAlbums: $onlyAlbums
            )->simplePaginate(config('spotifyConfig.pagination'));
        }

        return $paginator;
    }

    /**
     * @param array $categoryIds
     * @param string $country
     * @param int $onlyAlbums
     * @param int $page
     * @return Paginator
     */
    public function getReleaseAlbumsPaginator(array $categoryIds, string $country, int $onlyAlbums, int $page): Paginator
    {
        sort($categoryIds);
        $categoryIdsString = implode(',', $categoryIds);
        $releasesCacheKey = "releases={$categoryIdsString}::country={$country}::only_albums={$onlyAlbums}";
        if (Cache::has($releasesCacheKey)) {
            $paginator = $this->getCachedAlbumsPaginator(
                cacheKey: $releasesCacheKey,
                page: $page,
                rout: route('releases')
            );
        } else {
            WarmUpNewReleaseAlbumsCache::dispatch($country)->onQueue('high');

            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });

            $paginator = $this->getReleaseAlbumsQueryBuilder(
                country: $country,
                onlyAlbums: $onlyAlbums,
                categoryIds: $categoryIds
            )->simplePaginate(config('spotifyConfig.pagination'));
        }

        return $paginator;
    }

    /**
     * @param User $user
     * @param int $onlyAlbums
     * @return Builder
     */
    public function getFollowedAlbumsQueryBuilder(User $user, int $onlyAlbums): Builder
    {
        $country = $user->country;
        $artistIds = $user->artists()->has('albums')->get()->pluck('id')->all();

        return Album::whereIn('artist_id', $artistIds)
            ->whereJsonContains('markets', $country)
            ->when($onlyAlbums, function ($query) {
                $query->where('type', 'album');
            })->orderBy('release_date', 'desc')
            ->orderBy('popularity', 'desc');
    }

    /**
     * @param string $country
     * @param int $onlyAlbums
     * @param array $categoryIds
     * @return Builder
     */
    public function getReleaseAlbumsQueryBuilder(string $country, int $onlyAlbums, array $categoryIds): Builder
    {
        $genreIds = Genre::whereHas('categories', function (Builder $query) use ($categoryIds) {
            $query->whereIn('id', $categoryIds);
        })->get()->unique('id')->pluck('id')->all();

        $artistIds = Connection::whereIn('genre_id', $genreIds)->get()->unique('artist_id')->pluck('artist_id')->all();

        return Album::whereIn('artist_id', $artistIds)
            ->whereJsonContains('markets', $country)
            ->when($onlyAlbums, function ($query) {
                $query->where('type', 'album');
            })->orderBy('release_date', 'desc')
            ->orderBy('popularity', 'desc');
    }

    /**
     * @param string $cacheKey
     * @param int $page
     * @param string $rout
     * @return Paginator
     */
    private function getCachedAlbumsPaginator(string $cacheKey, int $page, string $rout): Paginator
    {
        $albumIds = json_decode(Cache::get($cacheKey), true);
        $albumIds = array_slice(
            array: $albumIds,
            offset: ($page - 1) * config('spotifyConfig.pagination'),
            length: config('spotifyConfig.pagination') + 5,
            preserve_keys: true
        );
        $albumCacheKeys = Arr::map($albumIds, function ($value) {
            return 'album_id=' . $value;
        });
        $albums = Cache::many($albumCacheKeys);
        $albumsObjects = Arr::map($albums, function ($value, $key) {
            if ($value == null) {
                $albumId = str_replace('album_id=', '', $key);

                if (Cache::has('albums_cached')) {
                    WarmUpAlbumCache::dispatch($albumId)->onQueue('high');
                } else {
                    WarmUpAlbumsCache::dispatch()->onQueue('high');
                }

                return Album::with('artist')->find($albumId);
            }
            return json_decode($value);
        });

        return app(Paginator::class, [
            'items' => Arr::whereNotNull($albumsObjects),
            'perPage' => config('spotifyConfig.pagination'),
            'currentPage' => $page,
            'options' => ['path' => $rout],
        ]);
    }
}
