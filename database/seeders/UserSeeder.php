<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $employeeRole = Role::where('name', RoleEnum::Employee->value)->firstOrFail();
        $financeRole = Role::where('name', RoleEnum::Finance->value)->firstOrFail();

        // Employees across different countries and currencies.
        $employees = [
            ['name' => 'Ana Costa', 'email' => 'ana@example.com', 'country' => 'Brazil', 'currency' => 'BRL'],
            ['name' => 'John Smith', 'email' => 'john@example.com', 'country' => 'United States', 'currency' => 'USD'],
            ['name' => 'Emma Brown', 'email' => 'emma@example.com', 'country' => 'United Kingdom', 'currency' => 'GBP'],
            ['name' => 'Yuki Tanaka', 'email' => 'yuki@example.com', 'country' => 'Japan', 'currency' => 'JPY'],
            ['name' => 'Liam Tremblay', 'email' => 'liam@example.com', 'country' => 'Canada', 'currency' => 'CAD'],
        ];

        foreach ($employees as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'country' => $data['country'],
                    'currency' => $data['currency'],
                ],
            );

            $user->roles()->syncWithoutDetaching([$employeeRole->id]);
        }

        // Finance team member.
        $finance = User::firstOrCreate(
            ['email' => 'finance@example.com'],
            [
                'name' => 'Sofia Martins',
                'password' => Hash::make('password'),
                'country' => 'Portugal',
                'currency' => 'EUR',
            ],
        );

        $finance->roles()->syncWithoutDetaching([$financeRole->id]);
    }
}
