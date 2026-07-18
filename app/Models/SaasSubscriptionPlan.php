<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaasSubscriptionPlan extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    protected $table = 'saas_subscription_plans';

    protected $fillable = [
        'name',
        'description',
        'monthly_price',
        'currency',
        'max_users',
        'max_trainers',
        'is_active',
    ];

    public function gyms()
    {
        return $this->hasMany(Gym::class, 'current_plan_id');
    }
}
