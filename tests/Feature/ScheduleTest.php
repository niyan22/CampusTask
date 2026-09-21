<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->course = Course::factory()->for($this->user)->create(['name' => 'Jaringan Komputer']);
        $this->actingAs($this->user);
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'course_id' => $this->course->id,
            'day' => 2,
            'start_time' => '10:00',
            'end_time' => '12:30',
            'room' => 'R. 301',
        ], $overrides);
    }

    public function test_schedule_page_groups_sessions_by_day_in_time_order(): void
    {
        Schedule::factory()->for($this->user)->for($this->course)->create(['day' => 1, 'start_time' => '13:00', 'end_time' => '14:40', 'room' => 'Ruang Siang']);
        Schedule::factory()->for($this->user)->for($this->course)->create(['day' => 1, 'start_time' => '08:00', 'end_time' => '09:40', 'room' => 'Ruang Pagi']);
        Schedule::factory()->create(['room' => 'Ruang Orang Lain']);

        $this->get(route('schedule.index'))
            ->assertOk()
            ->assertSee('Jaringan Komputer')
            ->assertSeeInOrder(['Ruang Pagi', 'Ruang Siang'])
            ->assertDontSee('Ruang Orang Lain')
            ->assertSee('08:00 – 09:40');
    }

    public function test_schedule_page_asks_for_a_course_first_when_there_is_none(): void
    {
        $this->course->delete();

        $this->get(route('schedule.index'))->assertSee('Tambahkan mata kuliah dulu');
    }

    public function test_schedule_can_be_added(): void
    {
        $this->post(route('schedule.store'), $this->validData())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('schedules', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'day' => 2,
            'room' => 'R. 301',
        ]);
    }

    public function test_schedule_rejects_end_time_before_start_time(): void
    {
        $this->post(route('schedule.store'), $this->validData(['start_time' => '10:00', 'end_time' => '09:00']))
            ->assertSessionHasErrors(['end_time' => 'Jam selesai harus setelah Jam mulai.']);

        $this->assertDatabaseCount('schedules', 0);
    }

    public function test_schedule_rejects_invalid_day_and_time_format(): void
    {
        $this->post(route('schedule.store'), $this->validData(['day' => 9, 'start_time' => 'pagi']))
            ->assertSessionHasErrors(['day', 'start_time']);
    }

    public function test_schedule_rejects_a_course_owned_by_someone_else(): void
    {
        $foreignCourse = Course::factory()->create();

        $this->post(route('schedule.store'), $this->validData(['course_id' => $foreignCourse->id]))
            ->assertSessionHasErrors('course_id');

        $this->assertDatabaseCount('schedules', 0);
    }

    public function test_schedule_can_be_deleted(): void
    {
        $schedule = Schedule::factory()->for($this->user)->for($this->course)->create();

        $this->delete(route('schedule.destroy', $schedule))->assertRedirect();

        $this->assertModelMissing($schedule);
    }

    public function test_another_users_schedule_returns_404_and_stays_untouched(): void
    {
        $schedule = Schedule::factory()->create();

        $this->delete(route('schedule.destroy', $schedule))->assertNotFound();

        $this->assertModelExists($schedule);
    }
}
