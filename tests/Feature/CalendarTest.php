<?php

namespace Tests\Feature;

use App\Models\Course;
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

    public function test_calendar_colors_tasks_by_their_course(): void
    {
        $course = Course::factory()->for($this->user)->create(['name' => 'Jaringan', 'color' => '#2E9C6B']);
        Task::factory()->for($this->user)->for($course)->create(['title' => 'Tugas Warna', 'due_date' => today()]);

        $this->get(route('calendar'))
            ->assertSee('#2E9C6B', false)
            ->assertSee('Jaringan');
    }

    public function test_calendar_export_downloads_pending_tasks_as_ics(): void
    {
        $course = Course::factory()->for($this->user)->create(['name' => 'Basis Data']);
        Task::factory()->for($this->user)->for($course)->create([
            'title' => 'Laporan, revisi; final',
            'description' => "Baris satu\nBaris dua",
            'due_date' => '2026-10-05',
        ]);
        Task::factory()->for($this->user)->done()->create(['title' => 'Sudah selesai']);
        Task::factory()->create(['title' => 'Tugas Orang Lain']);

        $response = $this->get(route('calendar.export'))->assertOk();

        $this->assertStringStartsWith('text/calendar', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('tugas-kampus.ics', $response->headers->get('Content-Disposition'));

        $body = $response->getContent();
        $this->assertStringStartsWith("BEGIN:VCALENDAR\r\n", $body);
        $this->assertStringEndsWith("END:VCALENDAR\r\n", $body);
        $this->assertSame(1, substr_count($body, 'BEGIN:VEVENT'));
        $this->assertStringContainsString("DTSTART;VALUE=DATE:20261005\r\n", $body);
        $this->assertStringContainsString("DTEND;VALUE=DATE:20261006\r\n", $body);
        $this->assertStringContainsString('SUMMARY:Laporan\, revisi\; final (Basis Data)', $body);
        $this->assertStringContainsString('DESCRIPTION:Baris satu\nBaris dua', $body);
        $this->assertStringNotContainsString('Sudah selesai', $body);
        $this->assertStringNotContainsString('Tugas Orang Lain', $body);
    }

    public function test_calendar_export_is_empty_but_valid_without_tasks(): void
    {
        $body = $this->get(route('calendar.export'))->assertOk()->getContent();

        $this->assertStringContainsString('BEGIN:VCALENDAR', $body);
        $this->assertStringNotContainsString('BEGIN:VEVENT', $body);
    }
}
