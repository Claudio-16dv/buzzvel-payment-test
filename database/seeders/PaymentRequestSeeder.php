<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentRequestSeeder extends Seeder
{
    public function run(): void
    {
        $financeId = User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Finance->value))->value('id');

        $employees = User::whereHas('roles', fn ($q) => $q->where('name', RoleEnum::Employee->value))->get();

        foreach ($employees as $employee) {
            PaymentRequest::factory()->pending()->create([
                'user_id' => $employee->id,
                'currency' => $employee->currency,
            ]);

            PaymentRequest::factory()->approved()->create([
                'user_id' => $employee->id,
                'currency' => $employee->currency,
                'reviewed_by' => $financeId,
            ]);

            PaymentRequest::factory()->rejected()->create([
                'user_id' => $employee->id,
                'currency' => $employee->currency,
                'reviewed_by' => $financeId,
            ]);
        }
    }
}
