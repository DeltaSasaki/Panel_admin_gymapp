<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryProduct extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'inventory_products';

    protected $fillable = [
        'gym_id',
        'category_id',
        'name',
        'description',
        'sku',
        'price',
        'cost_price',
        'currency',
        'stock_quantity',
        'min_stock',
        'unit',
        'image_url',
        'is_available',
        'is_food',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'product_id');
    }
}
