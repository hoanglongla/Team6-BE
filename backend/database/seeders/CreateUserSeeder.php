<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CreateUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            [
                "username" => "admin",
                "email" => "admin@wifosoft.com",
                "password" => \Hash::make('admin')
            ],
            [
                "username" => "daily1",
                "email" => "daily1@wifosoft.com",
                "password" => \Hash::make('admin')
            ],
            [
                "username" => "daily2",
                "email" => "daily2@wifosoft.com",
                "password" => \Hash::make('admin')
            ],
        ]);
    }

}
