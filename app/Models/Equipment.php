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
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }
}
