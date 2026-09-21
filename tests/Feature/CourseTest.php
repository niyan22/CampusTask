<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
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
            'name' => 'Basis Data',
            'color' => Course::COLORS[0],
            'lecturer' => 'Dr. Andi',
            'credits' => 3,
        ], $overrides);
    }

    public function test_course_list_shows_only_own_courses_with_pending_task_count(): void
    {
        $course = Course::factory()->for($this->user)->create(['name' => 'Algoritma']);
        Task::factory()->for($this->user)->for($course)->count(2)->create();
        Task::factory()->for($this->user)->for($course)->done()->create();
        Course::factory()->create(['name' => 'Kuliah Orang Lain']);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Algoritma')
            ->assertSee('2 tugas belum selesai')
            ->assertDontSee('Kuliah Orang Lain');
    }

    public function test_course_can_be_created(): void
    {
        $this->post(route('courses.store'), $this->validData())
            ->assertRedirect(route('courses.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('courses', ['user_id' => $this->user->id, 'name' => 'Basis Data', 'credits' => 3]);
    }

    public function test_course_name_must_be_unique_per_user_only(): void
    {
        Course::factory()->for($this->user)->create(['name' => 'Basis Data']);
        Course::factory()->create(['name' => 'Statistika']);

        $this->post(route('courses.store'), $this->validData(['name' => 'Basis Data']))
            ->assertSessionHasErrors(['name' => 'Nama sudah digunakan.']);

        // Nama yang dipakai user lain tidak menghalangi.
        $this->post(route('courses.store'), $this->validData(['name' => 'Statistika']))
            ->assertSessionHasNoErrors();
    }

    public function test_course_rejects_unknown_color_and_bad_credits(): void
    {
        $this->post(route('courses.store'), $this->validData(['color' => 'red;background:url(x)', 'credits' => 20]))
            ->assertSessionHasErrors(['color', 'credits']);

        $this->assertDatabaseCount('courses', 0);
    }

    public function test_course_can_be_updated_and_keep_its_own_name(): void
    {
        $course = Course::factory()->for($this->user)->create(['name' => 'Basis Data']);

        $this->put(route('courses.update', $course), $this->validData(['lecturer' => 'Dosen Baru']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('courses.index'));

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'lecturer' => 'Dosen Baru']);
    }

    public function test_deleting_a_course_keeps_tasks_but_removes_schedules(): void
    {
        $course = Course::factory()->for($this->user)->create();
        $task = Task::factory()->for($this->user)->for($course)->create();
        $schedule = Schedule::factory()->for($this->user)->for($course)->create();

        $this->delete(route('courses.destroy', $course))->assertRedirect(route('courses.index'));

        $this->assertModelMissing($course);
        $this->assertModelMissing($schedule);
        $this->assertNull($task->fresh()->course_id);
    }

    public function test_another_users_course_returns_404_and_stays_untouched(): void
    {
        $course = Course::factory()->create(['name' => 'Milik Orang Lain']);

        $this->get(route('courses.edit', $course))->assertNotFound();
        $this->put(route('courses.update', $course), $this->validData())->assertNotFound();
        $this->delete(route('courses.destroy', $course))->assertNotFound();

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'name' => 'Milik Orang Lain']);
    }
}
