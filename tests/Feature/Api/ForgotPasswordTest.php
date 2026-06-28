<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_a_password_reset_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'jane@example.com']);

        $this->postJson('/api/forgot-password', [
            'email' => 'JANE@example.com',
        ])->assertOk()->assertJsonPath(
            'message',
            'If an account exists for that email, a password reset link has been sent.'
        );

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_email_links_to_the_frontend_with_token_and_email(): void
    {
        config(['app.frontend_url' => 'https://store.example.com']);
        $user = User::factory()->make([
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
        ]);

        $mail = (new ResetPasswordNotification('reset/token'))->toMail($user);

        $this->assertSame(
            'https://store.example.com/reset-password?token=reset%2Ftoken&email=jane%40example.com',
            $mail->actionUrl
        );
    }

    public function test_forgot_password_does_not_reveal_unknown_accounts(): void
    {
        Notification::fake();

        $this->postJson('/api/forgot-password', [
            'email' => 'unknown@example.com',
        ])->assertOk()->assertJsonPath(
            'message',
            'If an account exists for that email, a password reset link has been sent.'
        );

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_a_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('OldSecure!Pass123'),
        ]);
        $oldToken = $user->createToken('old-session');
        $resetToken = Password::createToken($user);

        $this->postJson('/api/reset-password', [
            'token' => $resetToken,
            'email' => $user->email,
            'password' => 'NewSecure!Pass456',
            'password_confirmation' => 'NewSecure!Pass456',
        ])->assertOk()->assertJsonPath(
            'message',
            'Password reset successfully. You can now sign in.'
        );

        $this->assertTrue(Hash::check('NewSecure!Pass456', $user->fresh()->password));
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $oldToken->accessToken->id,
        ]);
    }

    public function test_invalid_reset_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'jane@example.com']);

        $this->postJson('/api/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'NewSecure!Pass456',
            'password_confirmation' => 'NewSecure!Pass456',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }
}
