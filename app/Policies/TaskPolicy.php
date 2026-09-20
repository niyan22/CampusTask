<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Edit, hapus, dan centang hanya boleh dilakukan pemilik tugas.
     * Tugas milik orang lain dijawab 404 supaya keberadaannya tidak bocor.
     */
    public function manage(User $user, Task $task): Response
    {
        return $user->id === $task->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
