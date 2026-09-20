<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_calendar_shows_own_tasks_in_the_requested_month(): void
    {
        Task::factory()->for($this->user)->create(['title' => 'Tugas September', 'due_date' => '2026-09-15']);
        Task::factory()->for($this->user)->create(['title' => 'Tugas Oktober', 'due_date' => '2026-10-15']);
        Task::factory()->create(['title' => 'Tugas Orang Lain', 'due_date' => '2026-09-15']);

        $this->get(route('calendar', ['month' => '2026-09']))
            ->assertOk()
            ->assertSee('September 2026')
            ->assertSee('Tugas September')
            ->assertDontSee('Tugas Oktober')
            ->assertDontSee('Tugas Orang Lain');
    }

    public function test_calendar_includes_tasks_on_the_first_and_last_day_of_the_grid(): void
    {
        // September 2026 dimulai hari Selasa dan berakhir hari Rabu, jadi grid berjalan 31 Agustus - 4 Oktober.
        Task::factory()->for($this->user)->create(['title' => 'Awal Grid', 'due_date' => '2026-08-31']);
        Task::factory()->for($this->user)->create(['title' => 'Akhir Grid', 'due_date' => '2026-10-04']);

        $this->get(route('calendar', ['month' => '2026-09']))
            ->assertSee('Awal Grid')
            ->assertSee('Akhir Grid');
    }

    public function test_calendar_defaults_to_current_month(): void
    {
        Task::factory()->for($this->user)->create(['title' => 'Tugas Bulan Ini', 'due_date' => today()]);

        $this->get(route('calendar'))
            ->assertOk()
            ->assertSee(now()->translatedFormat('F Y'))
            ->assertSee('Tugas Bulan Ini');
    }

    public function test_calendar_rejects_invalid_month(): void
    {
        $this->get(route('calendar', ['month' => 'bukan-bulan']))
            ->assertSessionHasErrors('month');
    }
}
