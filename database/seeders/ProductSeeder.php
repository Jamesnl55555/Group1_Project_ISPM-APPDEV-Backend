<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        foreach (range(1,5) as $userId) {
            Product::factory()->count(10)->create([
                'user_id' => $userId,
                'file_path' => 'placeholder.jpg'
            ]);
        }
    }
}