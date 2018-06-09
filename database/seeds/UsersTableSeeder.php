<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('users')->insert([
        //     'firstname' => "M",
        //     'lastname' => "F",
        //     'email' => "admin@epicschool.io",
        //     'email_confirmed' => true,
        //     'role_id' => 3, // 3 is admin role id
        //     'password' => app('hash')->make('12345678'),
        // ]);

        DB::table('users')->insert([
            'firstname' => "MaJeD",
            'lastname' => "FayaZi",
            'email' => "n4e.majed@gmail.com",
            'email_confirmed' => true,
            'role_id' => 3, // 3 is admin role id
            'password' => app('hash')->make('12345678'),

            'remember_token' => '',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]);
    }
}
