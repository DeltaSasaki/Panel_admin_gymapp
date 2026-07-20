<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTrainerAssignment extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'user_trainer_assignments';

    protected $fillable = [
        'user_id',
        'trainer_id',
        'assigned_at',
        'end_date',
        'is_active',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }
}
