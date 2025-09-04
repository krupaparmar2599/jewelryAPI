<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Coupon;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['amount', 'percentage']);
        return [
            'code' => strtoupper(Str::random(8)),
            'type' => $type,
            'value' => $type === 'percentage' ? rand(5, 30) : rand(50, 200),
            'max_discount' => $type === 'percentage' ? rand(100, 300) : null,
            'min_order_amount' => rand(100, 1000),
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(rand(10, 60)),
            'is_active' => 1,
        ];
    }
}
