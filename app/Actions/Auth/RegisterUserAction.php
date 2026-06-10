<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * @param  array{name: string, email: string, password: string, country: string, currency: string, role?: string}  $data
     */
    public function handle(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'country' => $data['country'],
            'currency' => strtoupper($data['currency']),
        ]);

        $roleName = $data['role'] ?? RoleEnum::Employee->value;
        $role = Role::where('name', $roleName)->firstOrFail();

        $user->roles()->attach($role);

        return $user->load('roles');
    }
}
