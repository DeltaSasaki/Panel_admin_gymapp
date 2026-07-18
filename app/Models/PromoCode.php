<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    protected $table = 'promo_codes';

    protected $fillable = [
        'gym_id',
        'code',
        'discount_type',
        'discount_value',
        'valid_from',
        'valid_until',
        'max_uses',
        'current_uses',
        'is_active',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function payments()
    {
        return $this->hasMany(MembershipPayment::class, 'promo_code_id');
    }
}
