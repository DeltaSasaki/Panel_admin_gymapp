<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type', // 'membership_expiry','payment_reminder','new_routine','achievement','general'
        'is_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
