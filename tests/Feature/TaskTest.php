<?php

namespace Tests\Feature;

use App\Models\Course;
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
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Laporan praktikum',
            'course_id' => Course::factory()->for($this->user)->create()->id,
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

    public function test_task_list_shows_course_name(): void
    {
        $course = Course::factory()->for($this->user)->create(['name' => 'Basis Data Lanjut']);
        Task::factory()->for($this->user)->for($course)->create();

        $this->get(route('tasks.index'))->assertSee('Basis Data Lanjut');
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

    public function test_task_list_search_also_matches_course_name(): void
    {
        $course = Course::factory()->for($this->user)->create(['name' => 'Kriptografi']);
        Task::factory()->for($this->user)->for($course)->create(['title' => 'Tugas Sandi']);
        Task::factory()->for($this->user)->create(['title' => 'Tugas Lain']);

        $this->get(route('tasks.index', ['q' => 'Kripto']))
            ->assertSee('Tugas Sandi')
            ->assertDontSee('Tugas Lain');
    }

    public function test_task_list_can_be_sorted_by_priority_or_newest(): void
    {
        // Urutan deadline: Rendah dulu. Urutan prioritas: Tinggi dulu.
        Task::factory()->for($this->user)->create(['title' => 'Tugas Rendah', 'priority' => 'low', 'due_date' => today()->addDay()]);
        $this->travel(1)->minutes();
        Task::factory()->for($this->user)->create(['title' => 'Tugas Tinggi', 'priority' => 'high', 'due_date' => today()->addDays(5)]);

        $this->get(route('tasks.index'))->assertSeeInOrder(['Tugas Rendah', 'Tugas Tinggi']);
        $this->get(route('tasks.index', ['sort' => 'priority']))->assertSeeInOrder(['Tugas Tinggi', 'Tugas Rendah']);
        $this->get(route('tasks.index', ['sort' => 'newest']))->assertSeeInOrder(['Tugas Tinggi', 'Tugas Rendah']);
    }

    public function test_task_list_is_paginated_by_ten(): void
    {
        Task::factory()->for($this->user)->count(12)->create();

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 10 && $tasks->total() === 12)
            ->assertSee('Halaman 1 dari 2');

        $this->get(route('tasks.index', ['page' => 2]))
            ->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 2);
    }

    public function test_task_is_created_for_the_logged_in_user(): void
    {
        $data = $this->validData();

        $this->post(route('tasks.store'), $data)
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $this->user->id,
            'course_id' => $data['course_id'],
            'title' => 'Laporan praktikum',
            'is_done' => false,
        ]);
    }

    public function test_task_can_be_created_without_a_course(): void
    {
        $this->post(route('tasks.store'), $this->validData(['course_id' => null]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', ['title' => 'Laporan praktikum', 'course_id' => null]);
    }

    public function test_task_rejects_a_course_owned_by_someone_else(): void
    {
        $foreignCourse = Course::factory()->create();

        $this->post(route('tasks.store'), $this->validData(['course_id' => $foreignCourse->id]))
            ->assertSessionHasErrors(['course_id' => 'Mata kuliah tidak ditemukan.']);

        $this->assertDatabaseCount('tasks', 0);
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

    public function test_task_toggle_marks_done_with_time_and_back(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $this->patch(route('tasks.toggle', $task));
        $this->assertTrue($task->fresh()->is_done);
        $this->assertNotNull($task->fresh()->completed_at);

        $this->patch(route('tasks.toggle', $task));
        $this->assertFalse($task->fresh()->is_done);
        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_task_can_be_deleted(): void
    {
        $task = Task::factory()->for($this->user)->create();

        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));

        $this->assertModelMissing($task);
    }

    public function test_create_and_edit_pages_render_with_course_options(): void
    {
        Course::factory()->for($this->user)->create(['name' => 'Jaringan Komputer']);
        $task = Task::factory()->for($this->user)->create();

        $this->get(route('tasks.create'))->assertOk()->assertSee('Simpan Tugas')->assertSee('Jaringan Komputer');
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
