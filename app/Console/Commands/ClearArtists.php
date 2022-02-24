<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ClearArtists as ClearArtistsJobs;
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
    protected $signature = 'app:queue-clear-artists';

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
     * @return void
     */
    public function handle()
    {
        try {
            ClearArtistsJobs::dispatch();
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['method' => __METHOD__]);
        }
    }
}
