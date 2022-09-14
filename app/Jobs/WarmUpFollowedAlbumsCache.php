<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Releases;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class WarmUpFollowedAlbumsCache implements ShouldQueue
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
        private readonly User $user
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
    public function handle(Releases $releases)
    {
        for ($onlyAlbums = 0; $onlyAlbums <= 1; $onlyAlbums++) {
            $followedAlbumsQueryBuilder = $releases->getFollowedAlbumsQueryBuilder(
                user: $this->user,
                onlyAlbums: $onlyAlbums,
            );
            $followedAlbumIds = $followedAlbumsQueryBuilder
                ->get('id')
                ->pluck('id');

            $followedCacheKey = "followed={$this->user->id}::only_albums={$onlyAlbums}";

            Cache::put(
                key: $followedCacheKey,
                value: $followedAlbumIds->toJson(),
                ttl: config('spotifyConfig.cache_ttl')
            );
        }
    }
}
