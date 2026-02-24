<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'name',
        'price',
        'type',
        'image_path',
    ];

    public function characters()
    {
        return $this->belongsToMany(Character::class, 'character_items')
                    ->withPivot('equipped')
                    ->withTimestamps();
    }
}
