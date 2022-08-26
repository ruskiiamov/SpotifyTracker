<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissedGenresArtist extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_name',
        'genre_names',
    ];

    protected $casts = [
        'genre_names' => 'array',
    ];
}
