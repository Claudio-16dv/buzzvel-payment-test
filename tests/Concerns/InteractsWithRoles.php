<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Enums\Role as RoleEnum;
use App\Models\Role;
use App\Models\User;

trait InteractsWithRoles
{
    protected function createEmployee(array $attributes = []): User
    {
        return $this->createUserWithRole(RoleEnum::Employee->value, $attributes);
    }

    protected function createFinance(array $attributes = []): User
    {
        return $this->createUserWithRole(RoleEnum::Finance->value, $attributes);
    }

    protected function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role);

        return $user;
    }
}
