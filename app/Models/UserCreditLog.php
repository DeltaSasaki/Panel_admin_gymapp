<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCreditLog extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'user_credit_logs';

    protected $fillable = [
        'gym_id',
        'user_id',
        'membership_id',
        'payment_id',
        'received_by',
        'source',
        'type',
        'amount',
        'amount_ves',
        'exchange_rate',
        'payment_method',
        'reference_code',
        'daily_rate',
        'previous_credit',
        'days_added',
        'credit_used',
        'resulting_credit',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'previous_credit' => 'decimal:2',
        'credit_used' => 'decimal:2',
        'resulting_credit' => 'decimal:2',
        'days_added' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function membership()
    {
        return $this->belongsTo(UserMembership::class, 'membership_id');
    }

    public function payment()
    {
        return $this->belongsTo(MembershipPayment::class, 'payment_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }
}
