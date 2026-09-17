<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider loginRoles */
    public function test_status_one_cannot_log_in_even_with_correct_password(int $role): void
    {
        $user = User::factory()->create([
            'status' => 1,
            'role_as' => $role,
            'password' => Hash::make('Secure!Pass123'),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Secure!Pass123',
        ])->assertUnauthorized()
            ->assertJsonPath('status', 401)
            ->assertJsonPath('message', 'Locked account')
            ->assertJsonMissingPath('token');

        $this->assertSame(0, $user->tokens()->count());
    }

    /** @dataProvider loginRoles */
    public function test_active_user_can_log_in(int $role): void
    {
        $user = User::factory()->create([
            'status' => 0,
            'role_as' => $role,
            'password' => Hash::make('Secure!Pass123'),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Secure!Pass123',
        ])->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonStructure(['token']);

        $this->assertSame(1, $user->tokens()->count());
    }

    public function loginRoles(): array
    {
        return [
            'regular user' => [User::ROLE_USER],
            'admin' => [User::ROLE_ADMIN],
        ];
    }
}
