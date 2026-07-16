<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlan extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'meal_plans';

    protected $fillable = [
        'name',
        'description',
        'goal_type',
        'bmi_category',
        'duration_weeks',
        'daily_calories',
        'is_active',
    ];

    public function assignments()
    {
        return $this->hasMany(UserMealPlan::class, 'meal_plan_id');
    }

    public function days()
    {
        return $this->hasMany(MealPlanDay::class, 'meal_plan_id')->orderBy('day_number');
    }

    public function activeAssignmentsCount()
    {
        return $this->assignments()->where('is_active', 1)->count();
    }
}
