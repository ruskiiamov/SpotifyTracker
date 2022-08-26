<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Console\Command;

class GenresAnalyse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:genres-analyse {categoryName?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyse most repeated words in genre titles';

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
     * @return void
     */
    public function handle()
    {
        $categoryName = $this->argument('categoryName') ?? null;

        $words = [];

        if (isset($categoryName) && Category::where('name', $categoryName)->exists()) {
            $genres = Category::where('name', $categoryName)->first()->genres;
        } else {
            $genres = Genre::lazy();
        }

        foreach ($genres as $genre) {
            $separated = explode(' ', strtolower($genre->name));
            foreach ($separated as $item) {
                if (array_key_exists($item, $words)) {
                    $words[$item]++;
                } else {
                    $words[$item] = 1;
                }
            }
        }
        arsort($words);
        $result[] = [];
        foreach ($words as $word => $amount) {
            $result[] = [$word, $amount];
        }

        $this->table(['Word', 'Frequency'], $result);
    }
}
