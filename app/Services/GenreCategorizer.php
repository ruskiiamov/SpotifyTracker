<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\GenreCategorizerInterface;
use App\Models\Category;
use App\Models\Genre;
use App\Models\MissedGenresArtist;

class GenreCategorizer implements GenreCategorizerInterface
{
    /**
     * @param array $regularKeyWords
     * @param array $specialKeyWords
     * @param array $bannedGenreNames
     * @param string $other
     */
    public function __construct(
        private readonly array $regularKeyWords,
        private readonly array $specialKeyWords,
        private readonly array $bannedGenreNames,
        private readonly string $other
    ) {}

    public function categorize(Genre $genre): void
    {
        if (in_array($genre->name, $this->bannedGenreNames)) {
            $this->delete($genre);
            return;
        }

        $categoryIds = $this->getGenreCategoryIds($genre);
        $genre->categories()->sync($categoryIds);
    }

    /**
     * @param Genre $genre
     * @return void
     */
    private function delete(Genre $genre): void
    {
        $genre->artists()->detach();
        $genre->categories()->detach();

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
    }

    /**
     * @param Genre $genre
     * @return array
     */
    private function getGenreCategoryIds(Genre $genre): array
    {
        foreach ($this->specialKeyWords as $categoryName => $keyWords) {
            foreach ($keyWords as $keyWord) {
                if (str_contains(strtolower($genre->name), $keyWord)) {
                    MissedGenresArtist::whereJsonContains('genre_names', $genre->name)->delete();
                    $category = Category::where('name', $categoryName)->first();
                    return [$category->id];
                }
            }
        }

        $categoryNames = [];
        foreach ($this->regularKeyWords as $categoryName => $keyWords) {
            foreach ($keyWords as $keyWord) {
                if (str_contains(strtolower($genre->name), $keyWord)) {
                    $categoryNames[] = $categoryName;
                }
            }
        }

        if (!empty($categoryNames)) {
            MissedGenresArtist::whereJsonContains('genre_names', $genre->name)->delete();
            $categories = Category::whereIn('name', $categoryNames)->get();
            return $categories->pluck('id')->toArray();
        } else {
            $other = Category::where('name', $this->other)->first();
            return [$other->id];
        }
    }
}
