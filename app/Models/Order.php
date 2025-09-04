<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'payment_method',
        'total_amount',
        'status',
        'final_total',
    ];

    // public function items()
    // {
    //     return $this->hasMany(OrderItem::class);
    // }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
