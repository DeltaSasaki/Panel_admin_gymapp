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
        'slug',
        'current_plan_id',
        'subscription_status',
        'address',
        'phone',
        'email',
        'logo_url',
        'primary_color',
        'secondary_color',
        'timezone',
        'is_active',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'gym_id');
    }

    public function plan()
    {
        return $this->belongsTo(SaasSubscriptionPlan::class, 'current_plan_id');
    }
}
