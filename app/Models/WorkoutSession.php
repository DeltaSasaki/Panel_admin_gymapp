<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutSession extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'workout_sessions';

    protected $fillable = [
        'user_id',
        'routine_id',
        'routine_day_id',
        'session_date',
        'started_at',
        'ended_at',
        'duration_minutes',
        'calories_burned',
        'feeling',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function routine()
    {
        return $this->belongsTo(WorkoutRoutine::class, 'routine_id');
    }
}
