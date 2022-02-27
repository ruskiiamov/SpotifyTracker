<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Tracker;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AfterFirstLogin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Waiting period for artist's new releases check
     *
     * @var int
     */
    private int $checkAge;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(
        private User $user,
    ) {
        $this->checkAge = config('spotifyConfig.checkAge');
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
            $tracker->updateUserFollowedArtists($this->user);
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['method' => __METHOD__, 'user_id' => $this->user->id]);
        }

        $checkThreshold = $this->getCheckDateTimeThreshold();

        $this->user->artists()
            ->where('checked_at', '<', $checkThreshold)
            ->chunkById(200, function ($artists) {
                foreach ($artists as $artist) {
                    try {
                        AddLastArtistAlbum::dispatch($artist)->onQueue('high');
                    } catch (Exception $e) {
                        Log::error($e->getMessage(), ['method' => __METHOD__, 'artist_id' => $artist->id]);
                    }
                }
            });
    }

    /**
     * @return string
     */
    private function getCheckDateTimeThreshold(): string
    {
        return date('Y-m-d H:i:s', time() - $this->checkAge * 60 * 60);
    }
}
