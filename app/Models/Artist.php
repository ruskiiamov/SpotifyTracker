<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'spotify_id',
        'name',
        'checked_at',
    ];

    protected $visible = [
        'id',
        'name',
        'genres',
    ];

    protected $appends = [
        'genres'
    ];

    public function getGenresAttribute()
    {
        return $this->genres()->get();
    }

    public function followings()
    {
        return $this->hasMany(Following::class);
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'connections');
    }

    public function connections()
    {
        return $this->hasMany(Connection::class);
    }
}
