<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'is_deleted'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
