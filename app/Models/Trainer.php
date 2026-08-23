<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'trainers';

    protected $fillable = [
        'user_id',
        'gym_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialty',
        'certification',
        'experience_years',
        'photo_url',
        'bio',
        'is_active',
        'max_clients',
        'hire_date',
        'salary',
    ];

    protected $appends = [
        'full_name',
        'active_clients_count',
        'capacity_percentage',
        'available_slots',
        'total_experience_years',
        'tenure',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function assignedClients()
    {
        return $this->hasMany(UserTrainerAssignment::class, 'trainer_id')->where('is_active', 1);
    }

    public function allAssignments()
    {
        return $this->hasMany(UserTrainerAssignment::class, 'trainer_id');
    }

    public function routines()
    {
        return $this->hasMany(WorkoutRoutine::class, 'created_by');
    }

    public function getFullNameAttribute()
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function getActiveClientsCountAttribute()
    {
        return $this->assignedClients ? $this->assignedClients->count() : 0;
    }

    public function getCapacityPercentageAttribute()
    {
        $max = max(1, (int) ($this->max_clients ?? 20));
        $active = $this->active_clients_count;
        return min(100, (int) round(($active / $max) * 100));
    }

    public function getAvailableSlotsAttribute()
    {
        $max = max(1, (int) ($this->max_clients ?? 20));
        return max(0, $max - $this->active_clients_count);
    }

    /**
     * Calculate dynamic total experience years (base experience + years since hire_date).
     */
    public function getTotalExperienceYearsAttribute()
    {
        $baseExp = (int) ($this->attributes['experience_years'] ?? 0);
        
        if (!empty($this->hire_date)) {
            $hireDate = \Carbon\Carbon::parse($this->hire_date);
            $yearsInGym = (int) $hireDate->diffInYears(\Carbon\Carbon::now());
            return $baseExp + $yearsInGym;
        }
        
        return $baseExp;
    }

    /**
     * Get formatted tenure (antigüedad en el gimnasio).
     */
    public function getTenureAttribute()
    {
        if (empty($this->hire_date)) {
            return 'Sin fecha de ingreso';
        }
        
        $hire = \Carbon\Carbon::parse($this->hire_date);
        $now = \Carbon\Carbon::now();
        $years = (int) $hire->diffInYears($now);
        $months = (int) $hire->copy()->addYears($years)->diffInMonths($now);
        
        if ($years > 0 && $months > 0) {
            return "{$years} " . ($years === 1 ? 'año' : 'años') . " y {$months} " . ($months === 1 ? 'mes' : 'meses');
        } elseif ($years > 0) {
            return "{$years} " . ($years === 1 ? 'año' : 'años');
        } elseif ($months > 0) {
            return "{$months} " . ($months === 1 ? 'mes' : 'meses');
        } else {
            $days = (int) $hire->diffInDays($now);
            return "{$days} " . ($days === 1 ? 'día' : 'días');
        }
    }
}
