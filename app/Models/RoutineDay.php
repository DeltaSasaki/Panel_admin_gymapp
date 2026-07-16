<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutineDay extends Model
{
    public $timestamps = false;

    protected $table = 'routine_days';

    protected $fillable = [
        'routine_id',
        'day_number',
        'day_name',
        'focus_area',
    ];

    public function routine()
    {
        return $this->belongsTo(WorkoutRoutine::class, 'routine_id');
    }

    public function exercises()
    {
        return $this->hasMany(RoutineExercise::class, 'routine_day_id')->orderBy('order_index');
    }
}
