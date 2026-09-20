<?php

namespace Tests\Feature;

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
        Task::factory()->for($user)->overdue()->create(['course' => 'Basis Data']);
        Task::factory()->for($user)->done()->overdue()->create(['course' => 'Basis Data']);
        Task::factory()->for($user)->create(['course' => 'Algoritma']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertViewHas('stats', fn (array $stats) => $stats['overdue'] === 1);
        $response->assertViewHas('courses', fn ($courses) => $courses['Basis Data'] === ['total' => 2, 'done' => 1]
            && $courses['Algoritma'] === ['total' => 1, 'done' => 0]);
    }

    public function test_dashboard_shows_empty_state_without_tasks(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada tugas untuk dihitung');
    }
}
