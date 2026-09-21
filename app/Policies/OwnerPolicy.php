<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu aturan untuk semua data milik user (Task, Course, Schedule):
 * hanya pemilik yang boleh mengubahnya. Milik orang lain dijawab 404
 * supaya keberadaannya tidak bocor. Didaftarkan di AppServiceProvider.
 */
class OwnerPolicy
{
    public function manage(User $user, Model $model): Response
    {
        return $user->id === $model->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
