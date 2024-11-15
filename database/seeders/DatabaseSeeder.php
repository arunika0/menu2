<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Restaurants
        $restaurantA = Restaurant::create([
            'name' => 'Restoran A',
            'address' => 'Jl. Contoh No.1, Kota A',
            'description' => 'Restoran A menyajikan berbagai hidangan lezat untuk sarapan dan makan siang.',
            'image' => 'https://via.placeholder.com/150',
        ]);

        $restaurantB = Restaurant::create([
            'name' => 'Restoran B',
            'address' => 'Jl. Contoh No.2, Kota B',
            'description' => 'Restoran B spesialisasi dalam hidangan manis dan minuman segar.',
            'image' => 'https://via.placeholder.com/150',
        ]);

        // Categories
        $categoryBreakfast = Category::create([
            'name' => 'Breakfast',
            'restaurant_id' => $restaurantA->id,
        ]);

        $categoryLunch = Category::create([
            'name' => 'Lunch',
            'restaurant_id' => $restaurantA->id,
        ]);

        $categoryShakes = Category::create([
            'name' => 'Shakes',
            'restaurant_id' => $restaurantB->id,
        ]);

        $categoryDesserts = Category::create([
            'name' => 'Desserts',
            'restaurant_id' => $restaurantB->id,
        ]);

        // Menu Items
        MenuItem::create([
            'name' => 'Buttermilk Pancakes',
            'price' => 15.99,
            'description' => 'Delicious pancakes with syrup and fresh strawberries.',
            'image' => 'https://via.placeholder.com/150',
            'category_id' => $categoryBreakfast->id,
            'restaurant_id' => $restaurantA->id,
        ]);

        MenuItem::create([
            'name' => 'Godzilla Milkshake',
            'price' => 6.99,
            'description' => 'A huge milkshake topped with donuts and whipped cream.',
            'image' => 'https://via.placeholder.com/150',
            'category_id' => $categoryShakes->id,
            'restaurant_id' => $restaurantB->id,
        ]);

        // Users
        User::create([
            'username' => 'superadmin',
            'password' => Hash::make('superpassword'),
            'role' => 'super_admin',
        ]);

        User::create([
            'username' => 'adminA',
            'password' => Hash::make('passwordA'),
            'role' => 'restaurant_admin',
            'restaurant_id' => $restaurantA->id,
        ]);

        User::create([
            'username' => 'adminB',
            'password' => Hash::make('passwordB'),
            'role' => 'restaurant_admin',
            'restaurant_id' => $restaurantB->id,
        ]);
    }
}
