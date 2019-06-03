<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (User::where('email','ivanfc@gmail.com')->first() == null)
        {
            User::create([
                'email' => 'ivanfc@gmail.com',
                'password' => bcrypt('admin'),
                'name' => 'Ivan'
            ]);
        }

    }
}
