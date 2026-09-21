<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_only_own_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->count(3)->create();
        Task::factory()->for($user)->done()->create();
        Task::factory()->count(5)->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('stats', [
                'total' => 4,
                'done' => 1,
                'active' => 3,
                'overdue' => 0,
                'percent' => 25,
            ]);
    }

    public function test_dashboard_counts_overdue_and_groups_progress_by_course(): void
    {
        $user = User::factory()->create();
        $database = Course::factory()->for($user)->create(['name' => 'Basis Data', 'color' => '#2F80D0']);
        $algorithm = Course::factory()->for($user)->create(['name' => 'Algoritma']);

        Task::factory()->for($user)->for($database)->overdue()->create();
        Task::factory()->for($user)->for($database)->done()->overdue()->create();
        Task::factory()->for($user)->for($algorithm)->create();
        Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertViewHas('stats', fn (array $stats) => $stats['overdue'] === 1);
        $response->assertViewHas('courses', fn ($courses) => $courses['Basis Data'] === ['total' => 2, 'done' => 1, 'color' => '#2F80D0']
            && $courses['Algoritma']['total'] === 1
            && $courses['Tanpa mata kuliah']['total'] === 1);
    }

    public function test_dashboard_lists_the_five_nearest_pending_deadlines_overdue_first(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->overdue()->create(['title' => 'Sudah telat']);
        foreach (range(1, 6) as $day) {
            Task::factory()->for($user)->create(['title' => "Tugas hari {$day}", 'due_date' => today()->addDays($day)]);
        }
        Task::factory()->for($user)->done()->create(['title' => 'Sudah selesai', 'due_date' => today()]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertViewHas('upcoming', fn ($upcoming) => $upcoming->count() === 5
            && $upcoming->first()->title === 'Sudah telat'
            && $upcoming->pluck('title')->doesntContain('Sudah selesai')
            && $upcoming->pluck('title')->doesntContain('Tugas hari 5'));
        $response->assertSeeInOrder(['Deadline terdekat', 'Sudah telat', 'Tugas hari 1']);
    }

    public function test_dashboard_counts_completed_tasks_per_week_for_the_last_six_weeks(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->count(2)->create(['is_done' => true, 'completed_at' => now()]);
        Task::factory()->for($user)->create(['is_done' => true, 'completed_at' => now()->subWeeks(2)]);
        Task::factory()->for($user)->create(['is_done' => true, 'completed_at' => now()->subWeeks(9)]);
        Task::factory()->for($user)->create(['is_done' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertViewHas('weekly', function ($weekly) {
                return $weekly->count() === 6
                    && $weekly->last()['count'] === 2
                    && $weekly[3]['count'] === 1
                    && $weekly->sum('count') === 3;
            });
    }

    public function test_dashboard_shows_empty_state_without_tasks(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada tugas untuk dihitung');
    }
}
