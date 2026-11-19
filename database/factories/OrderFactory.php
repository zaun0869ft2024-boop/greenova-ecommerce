<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'product_id'       => Product::factory(),
            'qty'              => fake()->numberBetween(1, 5),
            'status'           => 'Selesai',
            'date'             => now()->toDateString(),
            'recipient_name'   => fake()->name(),
            'recipient_phone'  => fake()->phoneNumber(),
            'shipping_address' => fake()->address(),
            'payment_method'   => 'Transfer Bank',
        ];
    }

    /** State: pesanan berstatus Pending */
    public function pending(): static
    {
        return $this->state(['status' => 'Pending']);
    }

    /** State: pesanan berstatus Dibatalkan */
    public function cancelled(): static
    {
        return $this->state(['status' => 'Dibatalkan']);
    }

    /** State: pesanan berstatus Selesai */
    public function completed(): static
    {
        return $this->state(['status' => 'Selesai']);
    }
}
