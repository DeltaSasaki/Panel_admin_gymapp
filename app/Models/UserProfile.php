<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'dni',
        'phone',
        'birth_date',
        'gender',
        'profile_photo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
