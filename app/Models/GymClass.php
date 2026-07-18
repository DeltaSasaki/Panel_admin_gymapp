<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GymClass extends Model
{
    public $timestamps = false;

    protected $table = 'gym_classes';

    protected $fillable = [
        'gym_id',
        'name',
        'description',
        'duration_minutes',
        'capacity',
        'color_code',
        'is_active',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'gym_class_id');
    }
}
