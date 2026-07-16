<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BodyMeasurement extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'body_measurements';

    protected $fillable = [
        'user_id',
        'weight_kg',
        'height_cm',
        'bmi',
        'bmi_category',
        'body_fat_pct',
        'muscle_mass_kg',
        'waist_cm',
        'hip_cm',
        'measured_at',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
