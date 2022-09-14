<?php

namespace App\Interfaces;

use App\Models\Genre;
use Illuminate\Support\Collection;

interface GenreCategorizerInterface
{
    /**
     * @param Genre $genre
     * @return void
     */
    public function categorize(Genre $genre): void;
    public function getCategoryIdsSets(): Collection;
}
