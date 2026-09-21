<?php

namespace App\Console\Commands;

use App\Mail\DeadlineReminder;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('tasks:send-reminders')]
#[Description('Kirim email pengingat untuk tugas yang deadline-nya besok')]
class SendDeadlineReminders extends Command
{
    public function handle(): int
    {
        $tomorrow = today()->addDay();

        $users = User::where('remind_by_email', true)
            ->with(['tasks' => fn ($query) => $query
                ->where('is_done', false)
                ->whereDate('due_date', $tomorrow)
                ->with('course')])
            ->get()
            ->filter(fn (User $user) => $user->tasks->isNotEmpty());

        foreach ($users as $user) {
            Mail::to($user)->send(new DeadlineReminder($user, $user->tasks));
        }

        $this->info("Pengingat dikirim ke {$users->count()} user.");

        return self::SUCCESS;
    }
}
