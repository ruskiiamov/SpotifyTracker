<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $fillable = [
        'spotify_id',
        'name',
        'release_date',
        'artist_id',
        'markets',
        'image',
        'popularity',
        'type'
    ];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }
}
