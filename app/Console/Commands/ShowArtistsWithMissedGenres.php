<?php

namespace App\Console\Commands;

use App\Models\MissedGenresArtist;
use Illuminate\Console\Command;

class ShowArtistsWithMissedGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:show-artists-with-missed-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show scanned artists with missed genres from DB';

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
        $data = [];

        MissedGenresArtist::chunk(200, function ($artists) use (&$data) {
            foreach ($artists as $artist) {
                foreach ($artist->genres as $genre) {
                    $data[] = [$artist->name, $genre->name];
                }
            }
        });

        $this->table(['Artist', 'Genres'], $data);
    }
}
