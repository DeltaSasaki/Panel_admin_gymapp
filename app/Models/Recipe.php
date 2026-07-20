<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'recipes';

    protected $fillable = [
        'gym_id',
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

    public function category()
    {
        return $this->belongsTo(RecipeCategory::class, 'category_id');
    }
}

class RecipeCategory extends Model
{
    public $timestamps = false;
    protected $table = 'recipe_categories';
    protected $fillable = ['gym_id', 'name', 'description'];
}
