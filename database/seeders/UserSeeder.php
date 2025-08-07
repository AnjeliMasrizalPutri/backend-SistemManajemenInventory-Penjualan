<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => '1',
        ]);

        User::create([
            'name' => 'Kasir',
            'email' => 'kasir@gmail.com',
            'password' => Hash::make('kasir123'),
            'role_id' => '2',
        ]);

        //   User::create([
        //     'name' => 'Kepala Gudang',
        //     'email' => 'kepgudang@gmail.com',
        //     'password' => Hash::make('kepgu123'),
        //     'role_id' => '3',
        // ]);
    }
}
