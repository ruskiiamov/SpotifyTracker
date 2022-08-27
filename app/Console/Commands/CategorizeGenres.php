<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Genre;
use App\Models\MissedGenresArtist;
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
        $bannedGenreNames = config('spotifyConfig.bannedGenreNames');

        Genre::chunk(200, function ($genres) use ($bannedGenreNames, $tracker) {
            foreach ($genres as $genre) {
                if (in_array($genre->name, $bannedGenreNames)) {
                    $genre->artists()->detach();

                    MissedGenresArtist::whereJsonContains('genre_names', $genre->name)
                        ->chunk(50, function ($artists) use ($genre) {
                            foreach ($artists as $artist) {
                                $missedGenres = $artist->genre_names;
                                $key = array_search($genre->name, $missedGenres);
                                array_splice($missedGenres, $key, 1);
                                if (empty($missedGenres)) {
                                    $artist->delete();
                                } else {
                                    $artist->update(['genre_names' => $missedGenres]);
                                }
                            }
                        });

                    $genre->delete();
                    continue;
                }

                $categoryName = $tracker->getGenreCategory($genre->name);
                if ($genre->category->name != $categoryName) {
                    $genre->update(['category_id' => Category::where('name', $categoryName)->first()->id]);
                }

                if ($categoryName != 'Other') {
                    MissedGenresArtist::whereJsonContains('genre_names', $genre->name)->delete();
                }
            }
        });
    }
}
