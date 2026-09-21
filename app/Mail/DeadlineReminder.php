<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class DeadlineReminder extends Mailable
{
    /**
     * @param  Collection<int, Task>  $tasks
     */
    public function __construct(public User $user, public Collection $tasks) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Pengingat: {$this->tasks->count()} tugas deadline besok");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.deadline-reminder');
    }
}
