<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineExercise extends Model
{
    public $timestamps = false;

    protected $table = 'routine_exercises';

    protected $fillable = [
        'routine_day_id',
        'exercise_id',
        'sets',
        'reps',
        'rest_seconds',
        'duration_seconds',
        'order_index',
        'notes',
    ];

    public function day()
    {
        return $this->belongsTo(RoutineDay::class, 'routine_day_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}
