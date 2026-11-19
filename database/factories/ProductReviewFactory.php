<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductReview>
 */
class ProductReviewFactory extends Factory
{
    protected $model = ProductReview::class;

    public function definition(): array
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();
        $order   = Order::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'status'     => 'Selesai',
        ]);

        return [
            'user_id'    => $user->id,
            'product_id' => $product->product_id,
            'order_id'   => $order->orders_id,
            'rating'     => fake()->numberBetween(1, 5),
            'comment'    => fake()->optional(0.7)->sentence(),
        ];
    }
}
