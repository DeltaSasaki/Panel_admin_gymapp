<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAssignedRoutine extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'user_assigned_routines';

    protected $fillable = [
        'user_id',
        'routine_id',
        'assigned_by',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function routine()
    {
        return $this->belongsTo(WorkoutRoutine::class, 'routine_id');
    }

    public function assigner()
    {
        return $this->belongsTo(Trainer::class, 'assigned_by');
    }
}
