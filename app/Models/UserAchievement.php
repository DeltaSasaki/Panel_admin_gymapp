<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    public $timestamps = false;

    protected $table = 'user_achievements';

    protected $fillable = [
        'user_id',
        'achievement_type',
        'description',
        'achieved_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
