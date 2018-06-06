<?php

use Illuminate\Database\Seeder;
use App\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = New Role;
        $role->name = 'guest';
        $role->description = 'Guest user';
        $role->save();

        $role = New Role;
        $role->name = 'user';
        $role->description = 'Normal user';
        $role->save();

        $role = New Role;
        $role->name = 'admin';
        $role->description = 'Administrator';
        $role->save();

    }
}
