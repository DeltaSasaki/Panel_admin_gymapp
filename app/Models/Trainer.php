<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'trainers';

    protected $fillable = [
        'user_id',
        'gym_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialty',
        'certification',
        'experience_years',
        'photo_url',
        'bio',
        'is_active',
        'hire_date',
        'salary',
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
