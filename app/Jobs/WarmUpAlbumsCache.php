<?php

namespace App\Jobs;

use App\Models\Album;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WarmUpAlbumsCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

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
     */
    public function handle()
    {
        if (!Cache::has('albums_cached')) {
            Cache::put('albums_cached', 1, 3600);
            Album::with('artist')->chunk(200, function ($albums) {
                foreach ($albums as $album) {
                    try {
                        Cache::put(
                            key: 'album_id=' . $album->id,
                            value: $album->toJson(),
                            ttl: config('spotifyConfig.cache_ttl')
                        );
                    } catch (Exception $e) {
                        Log::error($e->getMessage(), [
                            'method' => __METHOD__,
                            'album_id' => $album->id ?? null,
                        ]);
                    }
                }
            });
        }
    }
}
