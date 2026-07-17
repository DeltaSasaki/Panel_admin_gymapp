<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Custom timestamps
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'gym_id',
        'email',
        'password_hash',
        'role',
        'is_active',
        'email_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Override password authentication column.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Relations
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function trainer()
    {
        return $this->hasOne(Trainer::class, 'user_id');
    }

    public function assignedRoutines()
    {
        return $this->hasMany(UserAssignedRoutine::class, 'user_id');
    }

    public function activeRoutine()
    {
        return $this->hasOne(UserAssignedRoutine::class, 'user_id')->where('is_active', 1);
    }

    public function assignedMealPlans()
    {
        return $this->hasMany(UserMealPlan::class, 'user_id');
    }

    public function activeMealPlan()
    {
        return $this->hasOne(UserMealPlan::class, 'user_id')->where('is_active', 1);
    }

    public function bodyMeasurements()
    {
        return $this->hasMany(BodyMeasurement::class, 'user_id');
    }

    public function latestMeasurement()
    {
        return $this->hasOne(BodyMeasurement::class, 'user_id')->latestOfMany('measured_at');
    }

    public function workoutSessions()
    {
        return $this->hasMany(WorkoutSession::class, 'user_id');
    }

    public function memberships()
    {
        return $this->hasMany(UserMembership::class, 'user_id');
    }

    public function activeMembership()
    {
        return $this->hasOne(UserMembership::class, 'user_id')->where('status', 'active');
    }
}
