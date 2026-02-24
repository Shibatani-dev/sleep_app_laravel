<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SleepRecord extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'bedtime',
        'wakeup',
        'hours',
        'quality',
        'memo',
        'input_time',
        'trust_score',
        'points_earned',
    ];

    protected $casts = [
        'date' => 'date',
        'input_time' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
