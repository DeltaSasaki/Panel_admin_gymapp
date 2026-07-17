<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'product_id',
        'movement_type', // 'in', 'out', 'adjustment'
        'quantity',
        'previous_stock',
        'new_stock',
        'reason',
        'reference_id',
        'performed_by',
    ];

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
