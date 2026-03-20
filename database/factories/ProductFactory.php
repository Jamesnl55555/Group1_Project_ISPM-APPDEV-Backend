<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'quantity' => $this->faker->numberBetween(1, 100),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'category' => $this->faker->word(),
            'is_archived' => false,
            'file_path' => 'placeholder.jpg',
            'color_size' => $this->faker->randomElement(['Red-L', 'Blue-M', 'Green-S', null]),
        ];
    }
}