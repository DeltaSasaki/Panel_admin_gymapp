<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    public $timestamps = false;

    protected $table = 'equipment';

    protected $fillable = [
        'gym_id',
        'name',
        'description',
        'image_url',
        'requires_gym',
        'is_active',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'exercise_equipment', 'equipment_id', 'exercise_id');
    }
}
