<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Category;
use App\Models\MissedGenresArtist;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class ScanArtistsWithMissedGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scan-artists-with-missed-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan and add to DB artists with only Other genres';

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
        $regularCategoryIds = Category::where('name', '<>', 'Other')->get()->pluck('id')->toArray();

        $artists = Artist::has('genres')
            ->whereDoesntHave('genres', function (Builder $query) use ($regularCategoryIds) {
                $query->whereIn('category_id', $regularCategoryIds);
            })->get();

        foreach ($artists as $artist) {
            if (MissedGenresArtist::where('artist_name', $artist->name)->doesntExist()) {
                $genres = [];
                foreach ($artist->genres as $genre) {
                    $genres[] = $genre->name;
                }
                MissedGenresArtist::create([
                    'artist_name' => $artist->name,
                    'genre_names' => $genres,
                ]);
            }
        }
    }
}
