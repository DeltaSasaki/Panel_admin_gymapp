<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutRoutine extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'workout_routines';

    protected $fillable = [
        'name',
        'description',
        'goal_type',
        'bmi_min',
        'bmi_max',
        'bmi_category',
        'difficulty',
        'duration_weeks',
        'days_per_week',
        'requires_gym',
        'is_active',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(Trainer::class, 'created_by');
    }

    public function days()
    {
        return $this->hasMany(RoutineDay::class, 'routine_id')->orderBy('day_number');
    }

    public function assignments()
    {
        return $this->hasMany(UserAssignedRoutine::class, 'routine_id');
    }

    public function activeAssignmentsCount()
    {
        return $this->assignments()->where('is_active', 1)->count();
    }
}
