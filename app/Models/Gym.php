<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gym extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'gyms';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'logo_url',
        'timezone',
        'is_active',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'gym_id');
    }
}
