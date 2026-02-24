<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Character extends Model
{
    protected $fillable = [
        'user_id',
        'character_type_id',
        'name',
        'level',
        'points',
        'trust_score',
        'status',
        'consecutive_bad_sleep',
    ];

    protected $casts = [
        'level' => 'integer',
        'points' => 'integer',
        'trust_score' => 'integer',
        'consecutive_bad_sleep' => 'integer',
    ];

    /**
     * このキャラクターの持ち主（ユーザー）を取得
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * このキャラクターのベースとなったキャラクタータイプを取得
     * 例: $character->characterType で取得可能
     */
    public function characterType()
    {
        return $this->belongsTo(CharacterType::class);
    }
    
    public function items()
    {
        return $this->belongsToMany(Item::class, 'character_items')
        ->withPivot('equipped')
        ->withTimestamps();
    }

    public function equippedItems()
    {
        return $this->items()->wherePivot('equipped', true);
        
    }

    public function recalculateLevel(): void
{
    $thresholds = [1 => 0, 2 => 20, 3 => 30];
    foreach ([3, 2, 1] as $lv) {
        if ($this->points >= $thresholds[$lv]) {
            $this->level = $lv;
            break;
        }
    }
    $this->level = max(1, min(3, $this->level));
}
}
