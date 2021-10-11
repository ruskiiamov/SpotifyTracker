<?php

namespace App\Console\Commands;

use App\Services\Tasks;
use Illuminate\Console\Command;

class ClearAlbums extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:clear-albums';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete obsolete albums';

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
     */
    public function handle()
    {
        $this->line('Clearing...');
        $startTime = time();
        (new Tasks())->clearAlbums();
        $endTime = time();
        $duration = $endTime - $startTime;
        $this->info('Success: Albums table cleared | time: ' . $duration . ' seconds');
    }
}
