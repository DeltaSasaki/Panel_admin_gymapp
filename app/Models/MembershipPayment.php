<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipPayment extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'membership_payments';

    protected $fillable = [
        'membership_id',
        'user_id',
        'amount',
        'currency',
        'payment_method', // 'cash', 'card', 'transfer', 'other'
        'payment_date',
        'reference_code',
        'received_by',
        'receipt_url',
        'notes',
    ];

    public function membership()
    {
        return $this->belongsTo(UserMembership::class, 'membership_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
