<?php

namespace App\Console\Commands;

use App\Models\Artist;
use Illuminate\Console\Command;

class RemoveDoubledConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-doubled-connections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove doubled artist-genre connections';

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
        Artist::chunk(200, function ($artists) {
            foreach ($artists as $artist) {
                if ($artist->genres()->doesntExist()) {
                    continue;
                }
                $genres = $artist->genres->unique();
                $artist->connections()->delete();
                foreach ($genres as $genre) {
                    $artist->genres()->attach($genre->id);
                }
            }
        });
    }
}
