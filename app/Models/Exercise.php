<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'exercises';

    protected $fillable = [
        'gym_id',
        'category_id',
        'name',
        'description',
        'instructions',
        'muscle_group',
        'difficulty',
        'requires_equipment',
        'video_url',
        'image_url',
        'calories_per_min',
    ];

    public function category()
    {
        return $this->belongsTo(ExerciseCategory::class, 'category_id');
    }
}
class ExerciseCategory extends Model
{
    public $timestamps = false;
    protected $table = 'exercise_categories';
    protected $fillable = ['gym_id', 'name', 'description', 'icon_url'];
}
