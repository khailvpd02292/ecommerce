<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        DB::table('admins')->insert([
            'name' => 'admin',
            'email'=> 'admin@gmail.com',
            'password'=> bcrypt('password'),
            'birthday'=> '1999/05/05',
            'phone' => $faker->phoneNumber,
            'address' => $faker->address,
            'avatar' => NULL,
            'sex' => 0,
            'account_status_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
