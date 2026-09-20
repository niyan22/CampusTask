<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * @return array<string, string>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Laporan praktikum',
            'course' => 'Basis Data',
            'description' => 'Kumpulkan dalam bentuk PDF.',
            'priority' => 'high',
            'due_date' => today()->addDays(3)->toDateString(),
        ], $overrides);
    }

    public function test_home_redirects_to_dashboard(): void
    {
        $this->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_task_list_shows_only_own_tasks(): void
    {
        Task::factory()->for($this->user)->create(['title' => 'Tugas Saya']);
        Task::factory()->create(['title' => 'Tugas Orang Lain']);

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Tugas Saya')
            ->assertDontSee('Tugas Orang Lain');
    }

    public function test_task_list_can_be_filtered_and_searched(): void
    {
        Task::factory()->for($this->user)->create(['title' => 'Tugas Aktif']);
        Task::factory()->for($this->user)->done()->create(['title' => 'Tugas Selesai']);
        Task::factory()->for($this->user)->overdue()->create(['title' => 'Tugas Telat']);

        $this->get(route('tasks.index', ['filter' => 'done']))
            ->assertSee('Tugas Selesai')
            ->assertDontSee('Tugas Aktif');

        $this->get(route('tasks.index', ['filter' => 'overdue']))
            ->assertSee('Tugas Telat')
            ->assertDontSee('Tugas Aktif');

        $this->get(route('tasks.index', ['q' => 'Aktif']))
            ->assertSee('Tugas Aktif')
            ->assertDontSee('Tugas Selesai');
    }

    public function test_task_is_created_for_the_logged_in_user(): void
    {
        $this->post(route('tasks.store'), $this->validData())
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'title' => 'Laporan praktikum',
            'is_done' => false,
        ]);
    }

    public function test_task_rejects_invalid_input_with_indonesian_messages(): void
    {
        $this->post(route('tasks.store'), $this->validData(['title' => '', 'priority' => 'urgent']))
            ->assertSessionHasErrors([
                'title' => 'Judul tugas wajib diisi.',
                'priority' => 'Prioritas tidak valid.',
            ]);

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_task_can_be_updated(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $this->put(route('tasks.update', $task), $this->validData(['title' => 'Judul baru']))
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Judul baru']);
    }

    public function test_task_can_be_toggled_done_and_back(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $this->patch(route('tasks.toggle', $task));
        $this->assertTrue($task->fresh()->is_done);

        $this->patch(route('tasks.toggle', $task));
        $this->assertFalse($task->fresh()->is_done);
    }

    public function test_task_can_be_deleted(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));

        $this->assertModelMissing($task);
    }

    public function test_create_and_edit_pages_render(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $this->get(route('tasks.create'))->assertOk()->assertSee('Simpan Tugas');
        $this->get(route('tasks.edit', $task))->assertOk()->assertSee('Simpan Perubahan');
    }

    public function test_another_users_task_returns_404_and_stays_untouched(): void
    {
        $task = Task::factory()->create(['title' => 'Milik Orang Lain']);

        $this->get(route('tasks.edit', $task))->assertNotFound();
        $this->put(route('tasks.update', $task), $this->validData())->assertNotFound();
        $this->patch(route('tasks.toggle', $task))->assertNotFound();
        $this->delete(route('tasks.destroy', $task))->assertNotFound();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Milik Orang Lain', 'is_done' => false]);
    }

    public function test_only_the_owner_can_manage_a_task(): void
    {
        $task = Task::factory()->for($this->user)->create();
        $stranger = User::factory()->create();

        $this->assertTrue(Gate::forUser($this->user)->allows('manage', $task));
        $this->assertTrue(Gate::forUser($stranger)->denies('manage', $task));
    }

    public function test_deadline_label_describes_remaining_time(): void
    {
        $this->assertSame('Hari ini', Task::factory()->make(['due_date' => today()])->deadlineLabel());
        $this->assertSame('Besok', Task::factory()->make(['due_date' => today()->addDay()])->deadlineLabel());
        $this->assertSame('Terlambat 2 hari', Task::factory()->make(['due_date' => today()->subDays(2)])->deadlineLabel());
    }
}
