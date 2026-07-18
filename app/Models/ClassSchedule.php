<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    public $timestamps = false;

    protected $table = 'class_schedules';

    protected $fillable = [
        'gym_id',
        'gym_class_id',
        'trainer_id',
        'scheduled_date',
        'start_time',
        'end_time',
        'status',
    ];

    public function gymClass()
    {
        return $this->belongsTo(GymClass::class, 'gym_class_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function bookings()
    {
        return $this->hasMany(ClassBooking::class, 'class_schedule_id');
    }
}
