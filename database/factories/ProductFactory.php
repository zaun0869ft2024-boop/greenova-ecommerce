<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_title' => fake()->words(3, true),
            'product_price' => fake()->numberBetween(10000, 500000),
            'product_image' => 'placeholder.jpg',
            'product_desc'  => fake()->sentence(),
            'product_cat'   => Category::factory(),
            'stock'         => fake()->numberBetween(1, 100),
        ];
    }
}
