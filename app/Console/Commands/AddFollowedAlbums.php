<?php

namespace App\Console\Commands;

use App\Services\Tasks;
use Illuminate\Console\Command;

class AddFollowedAlbums extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:add-followed-albums';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new albums from followed artists for all users';

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
        $this->line('Adding...');
        $startTime = time();
        (new Tasks())->addFollowedAlbums();
        $endTime = time();
        $duration = $endTime - $startTime;
        $this->info('Success: New albums from followed artists added | time: ' . $duration . ' seconds');
    }
}
