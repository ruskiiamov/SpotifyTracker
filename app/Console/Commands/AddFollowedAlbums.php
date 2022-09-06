<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Facades\Spotify;
use App\Jobs\AddLastArtistAlbum;
use App\Models\Artist;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AddFollowedAlbums extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-add-followed-albums';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new albums from followed artists for all users';

    /**
     * Waiting period for artist's new releases check
     *
     * @var int
     */
    private int $checkAge;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->checkAge = config('spotifyConfig.checkAge');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (Spotify::areRequestsAvailable()) {
            $checkThreshold = $this->getCheckDateTimeThreshold();

            Artist::has('followings')
                ->where('checked_at', '<', $checkThreshold)
                ->chunkById(200, function ($artists) {
                    try {
                        AddLastArtistAlbum::dispatch($artists);
                    } catch (Exception $e) {
                        Log::error($e->getMessage(), ['method' => __METHOD__]);
                    }
                });
        }
    }

    /**
     * @return string
     */
    private function getCheckDateTimeThreshold(): string
    {
        return date('Y-m-d H:i:s', time() - $this->checkAge * 60 * 60);
    }
}
