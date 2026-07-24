<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'ingredients';

    protected $fillable = [
        'gym_id',
        'is_global',
        'name',
        'unit',
        'calories_per_100g',
        'protein_g',
        'carbs_g',
        'fat_g',
        'fiber_g',
        'is_active',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients', 'ingredient_id', 'recipe_id');
    }
}
