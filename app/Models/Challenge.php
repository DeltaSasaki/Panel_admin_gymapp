<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    protected $table = 'challenges';

    protected $fillable = [
        'gym_id',
        'title',
        'description',
        'goal_type',
        'routine_id',
        'exercise_id',
        'target_value',
        'target_unit',
        'start_date',
        'end_date',
        'xp_reward',
        'token_reward',
        'badge_id',
        'is_active',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function routine()
    {
        return $this->belongsTo(WorkoutRoutine::class, 'routine_id');
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }

    public function badge()
    {
        return $this->belongsTo(AchievementDefinition::class, 'badge_id');
    }

    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class, 'challenge_id');
    }

    public function getGoalLabelAttribute()
    {
        switch ($this->goal_type) {
            case 'routine':
                $routineName = $this->routine ? $this->routine->name : 'Rutina específica';
                return "Completar {$this->target_value} " . ($this->target_unit ?: 'sesiones') . " de la rutina '{$routineName}'";
            case 'exercise':
                $exName = $this->exercise ? $this->exercise->name : 'Ejercicio específico';
                return "Completar {$this->target_value} " . ($this->target_unit ?: 'repeticiones') . " de '{$exName}'";
            case 'attendance':
                return "Asistir {$this->target_value} " . ($this->target_unit ?: 'días') . " al gimnasio";
            default:
                return "Alcanzar {$this->target_value} " . ($this->target_unit ?: 'puntos / actividades');
        }
    }
}
