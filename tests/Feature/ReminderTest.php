<?php

namespace Tests\Feature;

use App\Mail\DeadlineReminder;
use App\Models\Course;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_is_sent_for_pending_tasks_due_tomorrow(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $course = Course::factory()->for($user)->create(['name' => 'Basis Data']);
        Task::factory()->for($user)->for($course)->create(['title' => 'Kuis besok', 'due_date' => today()->addDay()]);

        $this->artisan('tasks:send-reminders')->expectsOutput('Pengingat dikirim ke 1 user.')->assertSuccessful();

        Mail::assertSent(DeadlineReminder::class, function (DeadlineReminder $mail) use ($user) {
            $mail->assertHasSubject('Pengingat: 1 tugas deadline besok');
            $mail->assertSeeInHtml('Kuis besok');
            $mail->assertSeeInHtml('Basis Data');

            return $mail->hasTo($user->email);
        });
    }

    public function test_reminder_groups_all_of_a_users_tasks_into_one_email(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        Task::factory()->for($user)->count(3)->create(['due_date' => today()->addDay()]);

        $this->artisan('tasks:send-reminders');

        Mail::assertSentCount(1);
        Mail::assertSent(DeadlineReminder::class, fn (DeadlineReminder $mail) => $mail->tasks->count() === 3);
    }

    public function test_reminder_skips_done_tasks_other_dates_and_opted_out_users(): void
    {
        Mail::fake();
        $doneUser = User::factory()->create();
        Task::factory()->for($doneUser)->done()->create(['due_date' => today()->addDay()]);

        $todayUser = User::factory()->create();
        Task::factory()->for($todayUser)->create(['due_date' => today()]);

        $laterUser = User::factory()->create();
        Task::factory()->for($laterUser)->create(['due_date' => today()->addDays(2)]);

        $optedOut = User::factory()->create(['remind_by_email' => false]);
        Task::factory()->for($optedOut)->create(['due_date' => today()->addDay()]);

        $this->artisan('tasks:send-reminders')->expectsOutput('Pengingat dikirim ke 0 user.');

        Mail::assertNothingSent();
    }

    public function test_reminder_command_is_scheduled_daily_at_seven(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command, 'tasks:send-reminders'));

        $this->assertNotNull($event);
        $this->assertSame('0 7 * * *', $event->expression);
    }
}
