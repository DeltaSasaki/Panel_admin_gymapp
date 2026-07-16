<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMealPlan extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'user_meal_plans';

    protected $fillable = [
        'user_id',
        'meal_plan_id',
        'assigned_by',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_id');
    }

    public function assigner()
    {
        return $this->belongsTo(Trainer::class, 'assigned_by');
    }
}
