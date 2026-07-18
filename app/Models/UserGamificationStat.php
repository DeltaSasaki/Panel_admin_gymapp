<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGamificationStat extends Model
{
    const UPDATED_AT = 'updatedAt';
    const CREATED_AT = null;

    protected $table = 'user_gamification_stats';

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'gym_id',
        'total_xp',
        'current_level',
        'token_balance',
        'current_streak_days',
        'longest_streak_days',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }
}
