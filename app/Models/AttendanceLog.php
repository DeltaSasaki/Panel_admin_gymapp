<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    protected $table = 'attendance_logs';

    protected $fillable = [
        'gym_id',
        'user_id',
        'check_in',
        'check_out',
        'entry_method',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }
}
