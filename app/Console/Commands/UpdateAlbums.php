<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Album;
use App\Services\Tracker;
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
    protected $signature = 'spotify:update-albums';

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
     * @param Tracker $tracker
     * @return void
     */
    public function handle(Tracker $tracker)
    {
        Album::chunk(200, function ($albums) use ($tracker) {
            foreach ($albums as $album) {
                try {
                    $tracker->updateAlbum($album);//TODO Change to Job Detach
                } catch (Exception $e) {
                    Log::error($e->getMessage(), ['method' => __METHOD__, 'album_id' => $album->id]);
                }
            }
        });
    }
}
