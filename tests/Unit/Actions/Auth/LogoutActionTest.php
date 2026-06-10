<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\LogoutAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class LogoutActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_the_current_access_token(): void
    {
        $user = User::factory()->create();
        $newToken = $user->createToken('api');
        $tokenId = $newToken->accessToken->id;

        // Simulate the token used on the current request.
        $user->withAccessToken($newToken->accessToken);

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $tokenId]);

        (new LogoutAction())->handle($user);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
        $this->assertSame(0, PersonalAccessToken::count());
    }
}
