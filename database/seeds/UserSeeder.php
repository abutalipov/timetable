<?php

use Illuminate\Database\Seeder;
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
        DB::table('users')->insert([
            'id'=>12,
            'first_name'=>'admin',
            'second_name'=>'admin',
            'patronymic'=>'admin',
            'email' => 'admin@gmail.com',
            'password' => 'admin',
        ]);
    }
}
