<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    protected $visible = [
        'id',
        'name',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'genre_category');
    }

    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'connections');
    }
}
