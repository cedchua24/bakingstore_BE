<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_a_strong_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Baker',
            'email' => 'Jane@example.com',
            'password' => 'Secure!Pass123',
            'password_confirmation' => 'Secure!Pass123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('email', 'jane@example.com')
            ->assertJsonPath('role_as', User::ROLE_USER)
            ->assertJsonStructure(['id', 'name', 'email', 'token', 'expires_at']);

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('Secure!Pass123', $user->password));
        $this->assertSame(User::ROLE_USER, $user->role_as);
        $this->assertSame(User::STATUS_ACTIVE, $user->status);
    }

    public function test_public_registration_cannot_create_an_admin(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Not An Admin',
            'email' => 'user@example.com',
            'password' => 'Secure!Pass123',
            'password_confirmation' => 'Secure!Pass123',
            'role_as' => User::ROLE_ADMIN,
        ])->assertCreated()->assertJsonPath('role_as', User::ROLE_USER);

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'role_as' => User::ROLE_USER,
        ]);
    }

    public function test_access_token_expires_after_one_hour(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => 'Secure!Pass123',
            'password_confirmation' => 'Secure!Pass123',
        ])->assertCreated();

        $headers = [
            'Authorization' => 'Bearer '.$response->json('token'),
        ];

        $this->getJson('/api/user', $headers)->assertOk();

        $this->travel(61)->minutes();
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/user', $headers)->assertUnauthorized();
    }

    public function test_registration_rejects_weak_or_unconfirmed_passwords(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_user_management_routes_require_an_admin_token(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/register')->assertUnauthorized();
        $this->deleteJson('/api/register/'.$user->id)->assertUnauthorized();

        Sanctum::actingAs($user, ['server:user']);

        $this->getJson('/api/register')->assertForbidden();
        $this->deleteJson('/api/register/'.$user->id)->assertForbidden();
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Current!Pass123'),
        ]);
        $currentToken = $user->createToken('current', ['server:user']);
        $otherToken = $user->createToken('other', ['server:user']);

        $response = $this
            ->withToken($currentToken->plainTextToken)
            ->putJson('/api/change-password', [
                'current_password' => 'Current!Pass123',
                'password' => 'NewSecure!Pass456',
                'password_confirmation' => 'NewSecure!Pass456',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Password changed successfully.');

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecure!Pass456', $user->password));
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);
    }

    public function test_change_password_requires_current_password_and_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Current!Pass123'),
        ]);

        $this->putJson('/api/change-password', [
            'current_password' => 'Current!Pass123',
            'password' => 'NewSecure!Pass456',
            'password_confirmation' => 'NewSecure!Pass456',
        ])->assertUnauthorized();

        Sanctum::actingAs($user, ['server:user']);

        $this->putJson('/api/change-password', [
            'current_password' => 'Wrong!Password123',
            'password' => 'NewSecure!Pass456',
            'password_confirmation' => 'NewSecure!Pass456',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');

        $this->putJson('/api/change-password', [
            'current_password' => 'Current!Pass123',
            'password' => 'NewSecure!Pass456',
            'password_confirmation' => 'does-not-match',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');
    }
}
