<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ShowSpotifyAvailableSinceValue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-available-since';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show available since value';

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
     */
    public function handle()
    {
        $timestamp = Cache::get('spotify-requests-available-since');
        $this->info($timestamp ? date('Y-m-d H:i:s', $timestamp) : 'NULL');
    }
}
