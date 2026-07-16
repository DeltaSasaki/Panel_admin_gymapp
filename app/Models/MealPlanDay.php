<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealPlanDay extends Model
{
    public $timestamps = false;

    protected $table = 'meal_plan_days';

    protected $fillable = [
        'meal_plan_id',
        'day_number',
        'breakfast_recipe_id',
        'snack1_recipe_id',
        'lunch_recipe_id',
        'snack2_recipe_id',
        'dinner_recipe_id',
    ];

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_id');
    }

    public function breakfast()
    {
        return $this->belongsTo(Recipe::class, 'breakfast_recipe_id');
    }

    public function snack1()
    {
        return $this->belongsTo(Recipe::class, 'snack1_recipe_id');
    }

    public function lunch()
    {
        return $this->belongsTo(Recipe::class, 'lunch_recipe_id');
    }

    public function snack2()
    {
        return $this->belongsTo(Recipe::class, 'snack2_recipe_id');
    }

    public function dinner()
    {
        return $this->belongsTo(Recipe::class, 'dinner_recipe_id');
    }
}
