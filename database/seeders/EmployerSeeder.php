<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'     => 'Acme Corp',
            'email'    => 'employer@acme.test',
            'password' => Hash::make('12345678'),
            'role'     => 'employer',
        ]);
    }
}