<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'Test user',
            'email' => 'kumarajith1996+test@gmail.com',
            'password' => Hash::make('test')
        ]);

        $user->createToken('auth')->plainTextToken;
    }
}
