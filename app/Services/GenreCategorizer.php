<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\GenreCategorizerInterface;
use App\Models\Category;
use App\Models\Genre;
use App\Models\MissedGenresArtist;
use Illuminate\Support\Collection;

class GenreCategorizer implements GenreCategorizerInterface
{
    const ROCK = 'Rock/Metal/Punk';
    const POP_RB_SOUL = 'Pop/R&B/Soul';
    const HIP_HOP = 'Hip hop';
    const ELECTRONIC = 'Electronic';
    const FOLK = 'Folk/Country';
    const BLUES_JAZZ_FUNK = 'Blues/Jazz/Funk';
    const CLASSICAL = 'Classical';
    const WORLD = 'World';
    const OTHER = 'Other';

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

    /**
     * @param Genre $genre
     * @return void
     */
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
     * @return Collection
     */
    public function getCategoryIdsSets(): Collection
    {
        $categoryIds = Category::where('name', '<>', $this->other)->get()->pluck('id')->toArray();

        $categoryIdsSets = collect([[]]);
        foreach ($categoryIds as $element) {
            foreach ($categoryIdsSets as $combination) {
                $categoryIdsSets->push(array_merge([$element], $combination));
            }
        }
        $categoryIdsSets->shift();

        return $categoryIdsSets;
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
