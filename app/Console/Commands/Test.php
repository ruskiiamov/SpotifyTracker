<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Genre;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $genres = Genre::where('category_id', '15')->get();
        foreach ($genres as $genre) {
            $artists = $genre->artists()->get();
            echo '??? GENRE: ' . $genre->name . PHP_EOL;
            foreach ($artists as $artist) {
                echo 'ARTIST:' . $artist->name . PHP_EOL;
                $artistGenres = $artist->genres()->get();
                echo 'GENRES: ';
                foreach ($artistGenres as $artistGenre) {
                    echo $artistGenre->name . ' | ';
                }
                echo PHP_EOL;
                $albums = $artist->albums()->get();
                foreach ($albums as $album) {
                    echo 'https://open.spotify.com/album/' . $album->spotify_id . PHP_EOL;
                }
            }
            echo PHP_EOL, PHP_EOL;
        }
    }
}
