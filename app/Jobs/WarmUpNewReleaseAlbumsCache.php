<?php

namespace App\Jobs;

use App\Interfaces\GenreCategorizerInterface;
use App\Services\Releases;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class WarmUpNewReleaseAlbumsCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $market
    ) {}

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff(): array
    {
        return [1, 3, 5];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Releases $releases, GenreCategorizerInterface $genreCategorizer)
    {
        $categoryIdsSets = $genreCategorizer->getCategoryIdsSets();

        for ($onlyAlbums = 0; $onlyAlbums <= 1; $onlyAlbums++) {
            foreach ($categoryIdsSets as $categoryIds) {
                sort($categoryIds);
                $categoryIdsString = implode(',', $categoryIds);

                $releaseAlbumsQueryBuilder = $releases->getReleaseAlbumsQueryBuilder(
                    country: $this->market,
                    onlyAlbums: $onlyAlbums,
                    categoryIds: $categoryIds
                );
                $releaseAlbumIds = $releaseAlbumsQueryBuilder
                    ->get('id')
                    ->pluck('id');

                $releasesCacheKey = "releases={$categoryIdsString}::country={$this->market}::only_albums={$onlyAlbums}";

                Cache::put(
                    key: $releasesCacheKey,
                    value: $releaseAlbumIds->toJson(),
                    ttl: config('spotifyConfig.cache_ttl')
                );
            }
        }
    }
}
