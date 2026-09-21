<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Budi Santoso']);
        $this->actingAs($this->user);
    }

    /**
     * @return array<string, string>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Baru',
            'email' => $this->user->email,
            'nim' => '2310999999',
            'major' => 'Sistem Informasi',
        ], $overrides);
    }

    public function test_profile_page_shows_current_data(): void
    {
        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee($this->user->email);
    }

    public function test_profile_data_can_be_saved(): void
    {
        $this->put(route('profile.update'), $this->validData())
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Budi Baru',
            'nim' => '2310999999',
            'major' => 'Sistem Informasi',
        ]);
    }

    public function test_profile_keeps_old_password_when_password_fields_are_empty(): void
    {
        $this->put(route('profile.update'), $this->validData());

        $this->assertTrue(Hash::check('password', $this->user->fresh()->password));
    }

    public function test_profile_rejects_email_used_by_another_user(): void
    {
        User::factory()->create(['email' => 'lain@example.com']);

        $this->put(route('profile.update'), $this->validData(['email' => 'lain@example.com']))
            ->assertSessionHasErrors(['email' => 'Email sudah digunakan.']);
    }

    public function test_profile_can_keep_own_email(): void
    {
        $this->put(route('profile.update'), $this->validData())
            ->assertSessionHasNoErrors();
    }

    public function test_password_can_be_changed_with_current_password(): void
    {
        $this->put(route('profile.update'), $this->validData([
            'current_password' => 'password',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ]))->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('passwordbaru', $this->user->fresh()->password));
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $this->put(route('profile.update'), $this->validData([
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ]))->assertSessionHasErrors(['current_password' => 'Password saat ini wajib diisi.']);

        $this->put(route('profile.update'), $this->validData([
            'current_password' => 'salah',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ]))->assertSessionHasErrors(['current_password' => 'Password saat ini salah.']);

        $this->assertTrue(Hash::check('password', $this->user->fresh()->password));
    }

    public function test_avatar_can_be_uploaded_and_is_shown(): void
    {
        Storage::fake('public');

        $this->put(route('profile.update'), $this->validData(['avatar' => UploadedFile::fake()->image('foto.jpg', 300, 300)]))
            ->assertSessionHasNoErrors();

        $path = $this->user->fresh()->avatar;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->get(route('profile.edit'))->assertSee('storage/'.$path, false);
    }

    public function test_uploading_a_new_avatar_deletes_the_old_one(): void
    {
        Storage::fake('public');
        $this->put(route('profile.update'), $this->validData(['avatar' => UploadedFile::fake()->image('lama.jpg')]));
        $oldPath = $this->user->fresh()->avatar;

        $this->put(route('profile.update'), $this->validData(['avatar' => UploadedFile::fake()->image('baru.png')]));

        $newPath = $this->user->fresh()->avatar;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_saving_without_a_new_file_keeps_the_avatar(): void
    {
        Storage::fake('public');
        $this->put(route('profile.update'), $this->validData(['avatar' => UploadedFile::fake()->image('foto.jpg')]));
        $path = $this->user->fresh()->avatar;

        $this->put(route('profile.update'), $this->validData(['name' => 'Nama Baru']));

        $this->assertSame($path, $this->user->fresh()->avatar);
        Storage::disk('public')->assertExists($path);
    }

    public function test_avatar_can_be_removed(): void
    {
        Storage::fake('public');
        $this->put(route('profile.update'), $this->validData(['avatar' => UploadedFile::fake()->image('foto.jpg')]));
        $path = $this->user->fresh()->avatar;

        $this->put(route('profile.update'), $this->validData(['remove_avatar' => '1']));

        $this->assertNull($this->user->fresh()->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_avatar_rejects_non_images_and_large_files(): void
    {
        Storage::fake('public');

        $this->put(route('profile.update'), $this->validData(['avatar' => UploadedFile::fake()->create('virus.pdf', 100, 'application/pdf')]))
            ->assertSessionHasErrors('avatar');

        $this->put(route('profile.update'), $this->validData(['avatar' => UploadedFile::fake()->create('besar.jpg', 3000, 'image/jpeg')]))
            ->assertSessionHasErrors(['avatar' => 'Foto profil maksimal 2048 KB.']);

        $this->assertNull($this->user->fresh()->avatar);
    }

    public function test_email_reminder_can_be_turned_off_and_on(): void
    {
        $this->assertTrue($this->user->fresh()->remind_by_email);

        $this->put(route('profile.update'), $this->validData());
        $this->assertFalse($this->user->fresh()->remind_by_email);

        $this->put(route('profile.update'), $this->validData(['remind_by_email' => '1']));
        $this->assertTrue($this->user->fresh()->remind_by_email);
    }
}
