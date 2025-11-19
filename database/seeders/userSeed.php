<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class userSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Padel',
            'email' => 'admin@padel.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);
    }
}
