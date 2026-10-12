<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'username' => 'admin',
            'firstname' => 'NDepan',
            'lastname' => 'NBelakang',
            'email' => 'admin@bps.go.id',
            'level' => 1,
            'niplama' => 111111111,
            'password' => bcrypt('online')
        ]);
        DB::table('users')->insert([
            'username' => 'supervisor',
            'firstname' => 'NDepan',
            'lastname' => 'NBelakang',
            'email' => 'supervisor@bps.go.id',
            'level' => 2,
            'niplama' => 222222222,
            'password' => bcrypt('online')
        ]);
        DB::table('users')->insert([
            'username' => 'operator',
            'firstname' => 'NDepan',
            'lastname' => 'NBelakang',
            'email' => 'operator@bps.go.id',
            'level' => 3,
            'niplama' => 333333333,
            'password' => bcrypt('online')
        ]);
    }
}
