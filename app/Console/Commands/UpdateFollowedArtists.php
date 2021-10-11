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
    protected $signature = 'spotify:artists';

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
     * @return int
     */
    public function handle()
    {
        (new Tasks())->updateFollowedArtists();
        $this->info('Success: Followed artists list updated');
    }
}
