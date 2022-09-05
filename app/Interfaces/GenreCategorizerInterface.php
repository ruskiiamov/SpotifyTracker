<?php

namespace App\Interfaces;

use App\Models\Genre;

interface GenreCategorizerInterface
{
    /**
     * @param Genre $genre
     * @return void
     */
    public function categorize(Genre $genre): void;
}
