<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AchievementDefinition extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    protected $table = 'achievement_definitions';

    protected $fillable = [
        'gym_id',
        'name',
        'description',
        'xp_reward',
        'token_reward',
        'icon_url',
        'condition_type',
        'target_value',
        'is_active',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }
}
