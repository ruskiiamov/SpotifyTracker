<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'genre_category');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
