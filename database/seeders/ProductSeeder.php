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
        $description = "It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).";
        
        DB::table('products')->insert([
            'name' => 'shirts',
            'price' => '500',
            'category_id' => 1,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'jeans',
            'price' => '500',
            'category_id' => 1,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP4',
            'price' => '50000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'samsung',
            'price' => '70000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP5',
            'price' => '60000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP6',
            'price' => '70000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP7',
            'price' => '80000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP8',
            'price' => '70000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IP8',
            'price' => '70000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IPX',
            'price' => '70000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => '2022-01-01',
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IPXS',
            'price' => '100000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => '2021-04-05',
            'updated_at' => now(),
        ]);

        DB::table('products')->insert([
            'name' => 'IPXR',
            'price' => '90000',
            'category_id' => 5,
            'description' => $description,
            'created_at' => '2022-05-05',
            'updated_at' => now(),
        ]);

    }
}
