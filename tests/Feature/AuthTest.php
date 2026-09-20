<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_protected_pages_to_login(): void
    {
        foreach (['dashboard', 'tasks.index', 'calendar', 'profile.edit'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_guest_cannot_create_tasks(): void
    {
        $this->post(route('tasks.store'), ['title' => 'Tugas', 'course' => 'MK', 'priority' => 'low', 'due_date' => '2026-10-01'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_login_and_register_pages_render_for_guests(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Masuk');
        $this->get(route('register'))->assertOk()->assertSee('Buat akun');
    }

    public function test_user_can_log_in_with_correct_credentials(): void
    {
        $user = User::factory()->create(['email' => 'budi@example.com']);

        $this->post(route('login'), ['email' => 'budi@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->post(route('login'), ['email' => 'budi@example.com', 'password' => 'salah'])
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_user_can_register_and_is_logged_in(): void
    {
        $this->post(route('register'), [
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'siti@example.com', 'name' => 'Siti Aminah']);
        $this->assertAuthenticated();
    }

    public function test_register_rejects_duplicate_email_and_short_password(): void
    {
        User::factory()->create(['email' => 'siti@example.com']);

        $this->post(route('register'), [
            'name' => 'Siti',
            'email' => 'siti@example.com',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ])->assertSessionHasErrors([
            'email' => 'Email sudah digunakan.',
            'password' => 'Password minimal 8 karakter.',
        ]);

        $this->assertGuest();
    }

    public function test_logged_in_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect();
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
