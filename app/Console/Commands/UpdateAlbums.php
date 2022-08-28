<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Facades\Spotify;
use App\Jobs\UpdateAlbum;
use App\Models\Album;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateAlbums extends Command
{
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
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (Spotify::areRequestsAvailable()) {
            Album::chunk(200, function ($albums) {
                foreach ($albums as $album) {
                    try {
                        UpdateAlbum::dispatch($album);
                    } catch (Exception $e) {
                        Log::error($e->getMessage(), ['method' => __METHOD__, 'album_id' => $album->id]);
                    }
                }
            });
        }
    }
}
