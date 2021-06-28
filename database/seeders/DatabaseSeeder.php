<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
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
        $categories = Config::get('spotifyConfig.genreCategories');
        foreach ($categories as $category) {
            DB::table('categories')->insert(['name' => $category]);
        }
    }
}
