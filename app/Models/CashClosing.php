<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashClosing extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'cash_closings';

    protected $fillable = [
        'gym_id',
        'cashier_id',
        'closed_by',
        'closing_date',
        'register_type',
        'exchange_rate',
        'total_usd',
        'total_ves',
        'cash_usd',
        'card_usd',
        'transfer_usd',
        'other_usd',
        'cash_ves',
        'card_ves',
        'transfer_ves',
        'other_ves',
        'expected_cash_usd',
        'actual_cash_usd',
        'difference_usd',
        'expected_cash_ves',
        'actual_cash_ves',
        'difference_ves',
        'memberships_count',
        'sales_count',
        'status',
        'notes',
        'closed_at',
    ];

    protected $casts = [
        'closing_date' => 'date:Y-m-d',
        'closed_at' => 'datetime',
        'exchange_rate' => 'decimal:4',
        'total_usd' => 'decimal:2',
        'total_ves' => 'decimal:2',
        'cash_usd' => 'decimal:2',
        'card_usd' => 'decimal:2',
        'transfer_usd' => 'decimal:2',
        'other_usd' => 'decimal:2',
        'cash_ves' => 'decimal:2',
        'card_ves' => 'decimal:2',
        'transfer_ves' => 'decimal:2',
        'other_ves' => 'decimal:2',
        'expected_cash_usd' => 'decimal:2',
        'actual_cash_usd' => 'decimal:2',
        'difference_usd' => 'decimal:2',
        'expected_cash_ves' => 'decimal:2',
        'actual_cash_ves' => 'decimal:2',
        'difference_ves' => 'decimal:2',
        'memberships_count' => 'integer',
        'sales_count' => 'integer',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function cashier()
    {
        return $this->belongsTo(Cashier::class, 'cashier_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
