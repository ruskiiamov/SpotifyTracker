<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Tracker;
use Illuminate\Console\Command;

class CategorizeGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:categorize-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Categorize all genres in DB';

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
    public function handle(Tracker $tracker)
    {
        $tracker->categorizeGenres();
    }
}
