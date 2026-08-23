<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChallenge extends Model
{
    public $timestamps = false;

    protected $table = 'user_challenges';

    protected $fillable = [
        'user_id',
        'challenge_id',
        'progress_value',
        'status', // 'active', 'completed', 'failed'
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }

    public function progressPercentage()
    {
        $target = $this->challenge ? max(1, (int)$this->challenge->target_value) : 100;
        return min(100, (int) round(((int)$this->progress_value / $target) * 100));
    }
}
