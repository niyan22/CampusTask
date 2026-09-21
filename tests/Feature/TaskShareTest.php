<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskShareTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $friend;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Budi']);
        $this->friend = User::factory()->create(['name' => 'Siti', 'email' => 'siti@example.com']);

        $course = Course::factory()->for($this->user)->create(['name' => 'Basis Data', 'color' => '#2F80D0']);
        $this->task = Task::factory()->for($this->user)->for($course)->create(['title' => 'Makalah kelompok']);

        $this->actingAs($this->user);
    }

    public function test_task_is_shared_as_a_copy_in_the_friends_own_list(): void
    {
        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $copy = $this->friend->tasks()->firstOrFail();

        $this->assertSame('Makalah kelompok', $copy->title);
        $this->assertSame($this->task->id, $copy->source_task_id);
        $this->assertFalse($copy->is_done);
        $this->assertSame($this->task->due_date->toDateString(), $copy->due_date->toDateString());
    }

    public function test_friend_gets_a_matching_course_without_duplicating_an_existing_one(): void
    {
        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com']);

        $friendCourse = $this->friend->courses()->where('name', 'Basis Data')->firstOrFail();
        $this->assertSame('#2F80D0', $friendCourse->color);
        $this->assertSame($friendCourse->id, $this->friend->tasks()->first()->course_id);

        // Berbagi tugas kedua dari mata kuliah yang sama tidak membuat mata kuliah ganda.
        $second = Task::factory()->for($this->user)->for($this->task->course)->create();
        $this->post(route('tasks.share.store', $second), ['email' => 'siti@example.com']);

        $this->assertSame(1, $this->friend->courses()->where('name', 'Basis Data')->count());
    }

    public function test_friend_sees_who_shared_the_task_and_owner_sees_the_count(): void
    {
        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com']);

        $this->get(route('tasks.index'))->assertSee('Dibagikan ke 1 teman');

        $this->actingAs($this->friend)
            ->get(route('tasks.index'))
            ->assertSee('Makalah kelompok')
            ->assertSee('Dari Budi');
    }

    public function test_share_page_lists_status_of_each_friend(): void
    {
        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com']);
        $this->friend->tasks()->firstOrFail()->update(['is_done' => true]);

        $this->get(route('tasks.share', $this->task))
            ->assertOk()
            ->assertSee('Siti')
            ->assertSee('Selesai');
    }

    public function test_task_cannot_be_shared_with_yourself_or_twice_or_an_unknown_email(): void
    {
        $this->post(route('tasks.share.store', $this->task), ['email' => $this->user->email])
            ->assertSessionHasErrors(['email' => 'Kamu tidak bisa membagikan tugas ke dirimu sendiri.']);

        $this->post(route('tasks.share.store', $this->task), ['email' => 'tidakada@example.com'])
            ->assertSessionHasErrors(['email' => 'Email tidak ditemukan.']);

        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com']);
        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com'])
            ->assertSessionHasErrors(['email' => 'Tugas ini sudah dibagikan ke Siti.']);

        $this->assertSame(1, Task::where('source_task_id', $this->task->id)->count());
    }

    public function test_a_shared_copy_cannot_be_shared_again(): void
    {
        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com']);
        $copy = $this->friend->tasks()->firstOrFail();

        $this->actingAs($this->friend);

        $this->get(route('tasks.share', $copy))->assertNotFound();
        $this->post(route('tasks.share.store', $copy), ['email' => $this->user->email])->assertNotFound();
    }

    public function test_only_the_owner_can_open_or_use_the_share_page(): void
    {
        $this->actingAs($this->friend);

        $this->get(route('tasks.share', $this->task))->assertNotFound();
        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com'])->assertNotFound();

        $this->assertSame(0, $this->friend->tasks()->count());
    }

    public function test_copies_are_independent_of_the_original(): void
    {
        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com']);
        $copy = $this->friend->tasks()->firstOrFail();

        $this->actingAs($this->friend)->patch(route('tasks.toggle', $copy));

        $this->assertTrue($copy->fresh()->is_done);
        $this->assertFalse($this->task->fresh()->is_done);
    }

    public function test_deleting_the_original_keeps_the_friends_copy(): void
    {
        $this->post(route('tasks.share.store', $this->task), ['email' => 'siti@example.com']);
        $copy = $this->friend->tasks()->firstOrFail();

        $this->delete(route('tasks.destroy', $this->task));

        $this->assertNull($copy->fresh()->source_task_id);
    }
}
