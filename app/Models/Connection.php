<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'genre_id',
    ];

    public function genre()
    {
        return $this->belongsTo(Genre::class);
    }
}
