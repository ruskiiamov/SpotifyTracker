<?php

namespace App\Console\Commands;

use App\Services\Tasks;
use Illuminate\Console\Command;

class UpdateFollowedArtists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:update-followed-artists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update followed artists for all users';

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
        $this->line('Updating...');
        $startTime = time();
        (new Tasks())->updateFollowedArtists();
        $endTime = time();
        $duration = $endTime - $startTime;
        $this->info('Success: Followed artists updated | time: ' . $duration . ' seconds');
    }
}
