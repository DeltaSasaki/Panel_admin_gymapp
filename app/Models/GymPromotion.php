<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GymPromotion extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'gym_promotions';

    protected $fillable = [
        'gym_id',
        'plan_id',
        'title',
        'description',
        'months_count',
        'discount_pct',
        'promotional_price',
        'valid_until',
        'is_active',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function plan()
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id');
    }
}
