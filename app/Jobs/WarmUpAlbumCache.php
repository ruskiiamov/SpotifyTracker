<?php

declare(strict_types=1);

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

class WarmUpAlbumCache implements ShouldQueue
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
        private readonly int $albumId
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
     */
    public function handle()
    {
        $key = 'album_id=' . $this->albumId;

        if (!Cache::has($key)) {
            $album = Album::with('artist')->find($this->albumId);
            try {
                Cache::put(
                    key: $key,
                    value: $album->toJson(),
                    ttl: config('spotifyConfig.cache_ttl')
                );
            } catch (Exception $e) {
                Log::error($e->getMessage(), [
                    'method' => __METHOD__,
                    'album_id' => $this->albumId ?? null,
                ]);
            }
        }
    }
}
