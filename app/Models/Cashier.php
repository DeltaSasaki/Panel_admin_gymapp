<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cashier extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'cashiers';

    protected $fillable = [
        'user_id',
        'gym_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'shift',
        'hire_date',
        'salary',
        'photo_url',
        'notes',
        'is_active',
    ];

    protected $appends = [
        'full_name',
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

    public function membershipPayments()
    {
        return $this->hasMany(MembershipPayment::class, 'received_by', 'user_id');
    }

    public function productSales()
    {
        return $this->hasMany(ProductSale::class, 'sold_by', 'user_id');
    }

    public function getFullNameAttribute()
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

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
