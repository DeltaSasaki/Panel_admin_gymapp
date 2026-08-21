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

    protected $casts = [
        'xp_reward' => 'integer',
        'token_reward' => 'decimal:2',
        'target_value' => 'integer',
        'is_active' => 'boolean',
        'createdAt' => 'datetime',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class, 'achievement_definition_id');
    }

    public function awardedUsers()
    {
        return $this->belongsToMany(User::class, 'user_achievements', 'achievement_definition_id', 'user_id');
    }
}
