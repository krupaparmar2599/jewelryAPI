<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    protected $fillable = ['user_id', 'coupon_id'];
// protected $guarded = []; // Allows all fields

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
