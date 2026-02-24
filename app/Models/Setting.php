<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'user_id',
        'target_bedtime',
        'target_wakeup',
        'target_sleep_hours',
        'reminder_enabled',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
        
    }
}
