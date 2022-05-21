<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $faker = Factory::create();

        DB::table('users')->insert([
            'name' => 'user',
            'email'=> 'user@gmail.com',
            'password'=> bcrypt('password'),
            'birthday'=> '1999/05/05',
            'created_at' => now(),
            'updated_at' => now(),
            'phone' => $faker->phoneNumber,
            'address' => $faker->address,
            'avatar' => NULL,
            'sex' => 0,
            'account_status_id' => 1,
        ]);

        DB::table('users')->insert([
            'name' => 'user1',
            'email'=> 'user1@gmail.com',
            'password'=> bcrypt('password'),
            'birthday'=> '1999/05/05',
            'created_at' => now(),
            'updated_at' => now(),
            'phone' => $faker->phoneNumber,
            'address' => $faker->address,
            'avatar' => NULL,
            'sex' => 0,
            'account_status_id' => 1,
        ]);

    }
}
