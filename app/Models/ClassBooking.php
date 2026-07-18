<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassBooking extends Model
{
    const CREATED_AT = 'booked_at';
    const UPDATED_AT = null;

    protected $table = 'class_bookings';

    protected $fillable = [
        'class_schedule_id',
        'user_id',
        'status',
    ];

    public function schedule()
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
