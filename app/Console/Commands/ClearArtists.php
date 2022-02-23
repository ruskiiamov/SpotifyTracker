<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Tracker;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ClearArtists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:clear-artists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete artists without followers';

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
        try {
            $tracker->clearArtists();//TODO change to Job Detaching
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['method' => __METHOD__]);
        }
    }
}
