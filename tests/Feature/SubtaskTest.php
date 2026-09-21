<?php

namespace Tests\Feature;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->task = Task::factory()->for($this->user)->create();
        $this->actingAs($this->user);
    }

    public function test_task_list_shows_subtask_progress(): void
    {
        Subtask::factory()->for($this->task)->done()->create(['title' => 'Langkah pertama']);
        Subtask::factory()->for($this->task)->create(['title' => 'Langkah kedua']);

        $this->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('Langkah 1/2')
            ->assertSee('Langkah pertama')
            ->assertSee('Langkah kedua');
    }

    public function test_subtask_can_be_added_and_keeps_the_list_open(): void
    {
        $this->post(route('subtasks.store', $this->task), ['subtask' => 'Tulis kesimpulan'])
            ->assertRedirect()
            ->assertSessionHas('open_task', $this->task->id);

        $this->assertDatabaseHas('subtasks', ['task_id' => $this->task->id, 'title' => 'Tulis kesimpulan', 'is_done' => false]);
    }

    public function test_subtask_requires_a_title(): void
    {
        $this->post(route('subtasks.store', $this->task), ['subtask' => ''])
            ->assertSessionHasErrors(['subtask' => 'Langkah wajib diisi.']);

        $this->assertDatabaseCount('subtasks', 0);
    }

    public function test_subtask_can_be_toggled_and_deleted(): void
    {
        $subtask = Subtask::factory()->for($this->task)->create();

        $this->patch(route('subtasks.toggle', [$this->task, $subtask]));
        $this->assertTrue($subtask->fresh()->is_done);

        $this->patch(route('subtasks.toggle', [$this->task, $subtask]));
        $this->assertFalse($subtask->fresh()->is_done);

        $this->delete(route('subtasks.destroy', [$this->task, $subtask]));
        $this->assertModelMissing($subtask);
    }

    public function test_subtasks_are_deleted_together_with_their_task(): void
    {
        $subtask = Subtask::factory()->for($this->task)->create();

        $this->delete(route('tasks.destroy', $this->task));

        $this->assertModelMissing($subtask);
    }

    public function test_another_users_task_returns_404_for_every_subtask_action(): void
    {
        $foreignTask = Task::factory()->create();
        $foreignSubtask = Subtask::factory()->for($foreignTask)->create();

        $this->post(route('subtasks.store', $foreignTask), ['subtask' => 'Langkah'])->assertNotFound();
        $this->patch(route('subtasks.toggle', [$foreignTask, $foreignSubtask]))->assertNotFound();
        $this->delete(route('subtasks.destroy', [$foreignTask, $foreignSubtask]))->assertNotFound();

        $this->assertDatabaseCount('subtasks', 1);
        $this->assertFalse($foreignSubtask->fresh()->is_done);
    }

    public function test_subtask_cannot_be_reached_through_a_different_task_url(): void
    {
        $otherOwnTask = Task::factory()->for($this->user)->create();
        $subtask = Subtask::factory()->for($otherOwnTask)->create();

        $this->patch(route('subtasks.toggle', [$this->task, $subtask]))->assertNotFound();
        $this->delete(route('subtasks.destroy', [$this->task, $subtask]))->assertNotFound();

        $this->assertModelExists($subtask);
        $this->assertFalse($subtask->fresh()->is_done);
    }
}
