<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'recipes';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'instructions',
        'preparation_min',
        'goal_type',
        'bmi_category',
        'calories_total',
        'protein_g',
        'carbs_g',
        'fat_g',
        'servings',
        'image_url',
        'is_active',
    ];
}
