<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Album;
use App\Services\Tracker;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateAlbum implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The album for update or delete.
     *
     * @var Album
     */
    private Album $album;

    /**
     * Create a new job instance.
     *
     * @param Album $album
     */
    public function __construct (Album $album)
    {
        $this->album = $album;
    }

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
     * @param Tracker $tracker
     * @return void
     */
    public function handle(Tracker $tracker)
    {
        try {
            $tracker->updateAlbum($this->album);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['method' => __METHOD__, 'album_id' => $this->album->id]);
        }
    }
}
