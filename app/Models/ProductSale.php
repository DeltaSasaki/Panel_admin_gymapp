<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSale extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    protected $table = 'product_sales';

    protected $fillable = [
        'gym_id',
        'user_id',
        'sold_by',
        'total_amount',
        'payment_method', // 'cash', 'card', 'transfer', 'other'
        'sale_date',
        'notes',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'id');
    }
}
