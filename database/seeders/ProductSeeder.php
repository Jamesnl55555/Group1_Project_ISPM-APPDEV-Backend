<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        foreach (User::all() as $user) {
            Product::factory()->count(10)->create([
                'user_id' => $user->id,
                'file_path' => 'placeholder.jpg',
            ]);
        }
    }
}