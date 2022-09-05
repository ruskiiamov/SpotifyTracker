<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach (Genre::all() as $genre) {
            $genre->categories()->detach();
        }
        DB::table('categories')->truncate();

        $categories = config('genres.categories');
        foreach ($categories as $category) {
            DB::table('categories')->insert(['name' => $category]);
        }

        Artisan::call('app:categorize-genres');
    }
}
