<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Facades\Spotify;
use App\Jobs\UpdateAlbums as UpdateAlbumsJob;
use App\Models\Album;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateAlbums extends Command
{
    /** @var int */
    private readonly int $releaseAge;

    /** @var int */
    private readonly int $getSeveralAlbumsLimit;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-update-albums';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update albums popularity abd delete obsolete albums';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->releaseAge = config('spotifyConfig.releaseAge');
        $this->getSeveralAlbumsLimit = config('spotifyConfig.getSeveralAlbumsLimit');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $releaseDateThreshold = $this->getReleaseDateThreshold();
        Album::where('release_date', '<', $releaseDateThreshold)->delete();

        if (Spotify::areRequestsAvailable()) {
            Album::chunk($this->getSeveralAlbumsLimit, function ($albums) {
                $albumIds = $albums->pluck('id')->toArray();
                try {
                    UpdateAlbumsJob::dispatch($albumIds);
                } catch (Exception $e) {
                    Log::error($e->getMessage(), ['method' => __METHOD__, 'album_ids' => print_r($albumIds, true)]);
                }
            });
        }
    }

    /**
     * @return string
     */
    private function getReleaseDateThreshold(): string
    {
        return date('Y-m-d', time() - $this->releaseAge * 24 * 60 * 60);
    }
}
