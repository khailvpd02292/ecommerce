<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('orders')->insert([
            'user_id' => '1',
            'name' => 'user',
            'email' => 'user@gmail.com',
            'phone' => '(806) 441-5758',
            'address' => "688 Noelia Court North Zackary, PA 50359-3066",
            'total' => 500,
            'status' => 0,
            'payment_method' => 'stripe',
            'payment_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('orders')->insert([
            'user_id' => '1',
            'name' => 'user',
            'email' => 'user@gmail.com',
            'phone' => '(806) 441-5758',
            'address' => "688 Noelia Court North Zackary, PA 50359-3066",
            'total' => 1400,
            'status' => 1,
            'payment_method' => 'stripe',
            'payment_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'product_id' => '2',
            'order_id' => '1',
            'product_name' => 'jeans',
            'price' => '500',
            'quantity' => "1",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'product_id' => '2',
            'order_id' => '2',
            'product_name' => 'jeans',
            'price' => '700',
            'quantity' => "2",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
