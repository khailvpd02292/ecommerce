<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('products')->insert([
            'name' => 'shirts',
            'price' => '500',
            'category_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'jeans',
            'price' => '500',
            'category_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP4',
            'price' => '50000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'samsung',
            'price' => '70000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP5',
            'price' => '60000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP6',
            'price' => '70000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP7',
            'price' => '80000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP8',
            'price' => '70000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP8',
            'price' => '70000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IPX',
            'price' => '70000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IPXS',
            'price' => '100000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IPXR',
            'price' => '90000',
            'category_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
