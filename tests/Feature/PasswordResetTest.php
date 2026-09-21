<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders_and_is_linked_from_login(): void
    {
        $this->get(route('login'))->assertSee('Lupa password?');
        $this->get(route('password.request'))->assertOk()->assertSee('Kirim link reset');
    }

    public function test_reset_link_is_sent_to_a_registered_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'budi@example.com']);

        $this->post(route('password.email'), ['email' => 'budi@example.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_gets_the_same_answer_and_no_email(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'budi@example.com']);

        $known = $this->post(route('password.email'), ['email' => 'budi@example.com'])->getSession()->get('status');
        $unknown = $this->post(route('password.email'), ['email' => 'tidakada@example.com'])->getSession()->get('status');

        $this->assertSame($known, $unknown);
        Notification::assertCount(1);
    }

    public function test_reset_link_email_is_in_indonesian(): void
    {
        $user = User::factory()->create();
        $mail = (new ResetPassword('token-abc'))->toMail($user);

        $this->assertSame('Permintaan Reset Password', $mail->subject);
        $this->assertStringContainsString('Kamu menerima email ini', $mail->introLines[0]);
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com']);
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => 'budi@example.com']))
            ->assertOk()
            ->assertSee('budi@example.com');

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'budi@example.com',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('passwordbaru', $user->fresh()->password));
    }

    public function test_reset_message_is_shown_on_the_login_page(): void
    {
        $this->withSession(['status' => 'Password berhasil diubah.'])
            ->get(route('login'))
            ->assertSee('Password berhasil diubah.');
    }

    public function test_reset_rejects_an_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com']);

        $this->post(route('password.store'), [
            'token' => 'token-palsu',
            'email' => 'budi@example.com',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ])->assertSessionHasErrors(['email' => 'Link reset password tidak valid atau sudah kedaluwarsa.']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_reset_token_cannot_be_used_twice(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com']);
        $token = Password::createToken($user);
        $data = ['token' => $token, 'email' => 'budi@example.com', 'password' => 'passwordbaru', 'password_confirmation' => 'passwordbaru'];

        $this->post(route('password.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('password.store'), array_merge($data, ['password' => 'passwordlain', 'password_confirmation' => 'passwordlain']))
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('passwordbaru', $user->fresh()->password));
    }

    public function test_reset_requires_a_long_matching_password(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com']);

        $this->post(route('password.store'), [
            'token' => Password::createToken($user),
            'email' => 'budi@example.com',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ])->assertSessionHasErrors('password');
    }

    public function test_logged_in_users_are_redirected_away_from_the_forgot_password_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('password.request'))
            ->assertRedirect();
    }
}
