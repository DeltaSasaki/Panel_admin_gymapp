<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    public $timestamps = false;

    protected $table = 'sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'unit_price_ves',
        'subtotal',
        'subtotal_ves',
    ];

    public function sale()
    {
        return $this->belongsTo(ProductSale::class, 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }
}
