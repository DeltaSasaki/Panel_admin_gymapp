<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    public $timestamps = false;

    protected $table = 'user_achievements';

    protected $fillable = [
        'user_id',
        'achievement_definition_id',
        'achievement_type',
        'description',
        'achieved_at',
    ];

    protected $casts = [
        'achievement_definition_id' => 'integer',
        'achieved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function definition()
    {
        return $this->belongsTo(AchievementDefinition::class, 'achievement_definition_id');
    }

    public function achievement()
    {
        return $this->belongsTo(AchievementDefinition::class, 'achievement_definition_id');
    }
}
