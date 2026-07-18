<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    protected $table = 'challenges';

    protected $fillable = [
        'gym_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'xp_reward',
        'token_reward',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class, 'challenge_id');
    }
}
