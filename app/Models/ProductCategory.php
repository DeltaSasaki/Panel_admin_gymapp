<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    // Table product_categories has no timestamps in SQL dump
    public $timestamps = false;

    protected $table = 'product_categories';

    protected $fillable = [
        'gym_id',
        'name',
        'description',
        'icon_url',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function products()
    {
        return $this->hasMany(InventoryProduct::class, 'category_id');
    }
}
