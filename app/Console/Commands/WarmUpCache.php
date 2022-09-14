<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Facades\Spotify;
use App\Jobs\WarmUpAlbumsCache;
use App\Jobs\WarmUpFollowedAlbumsCache;
use App\Jobs\WarmUpNewReleaseAlbumsCache;
use App\Models\User;
use App\Services\Tracker;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WarmUpCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:warm-up-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up cache';

    /**
     * Markets for new releases searching
     *
     * @var array
     */
    private array $markets;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->markets = config('spotifyConfig.markets');
    }

    /**
     * Execute the console command.
     *
     */
    public function handle(Tracker $tracker)
    {
        $this->warmUpAlbums();
        $this->warmUpFollowedAlbums();
        $this->warmUpNewReleases($tracker);
    }

    /**
     * @return void
     */
    private function warmUpAlbums(): void
    {
        try {
            WarmUpAlbumsCache::dispatch();
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['method' => __METHOD__]);
        }
    }

    /**
     * @return void
     */
    private function warmUpFollowedAlbums(): void
    {
        User::chunk(200, function ($users) {
            foreach ($users as $user) {
                try {
                    WarmUpFollowedAlbumsCache::dispatch($user);
                } catch (Exception $e) {
                    Log::error($e->getMessage(), ['method' => __METHOD__, 'user_id' => $user->id]);
                }
            }
        });
    }

    /**
     * @param Tracker $tracker
     * @return void
     */
    private function warmUpNewReleases(Tracker $tracker): void
    {
        if (Spotify::areRequestsAvailable()) {
            $currentMarkets = $tracker->getCurrentMarkets();
            foreach ($this->markets as $market) {
                if (in_array($market, $currentMarkets)) {
                    try {
                        WarmUpNewReleaseAlbumsCache::dispatch($market);
                    } catch (Exception $e) {
                        Log::error($e->getMessage(), ['method' => __METHOD__, 'market' => $market]);
                    }
                }
            }
        }
    }
}
