<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['title', 'course', 'description', 'priority', 'due_date', 'is_done'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /** Pilihan prioritas: nilai di database => label di layar. */
    public const PRIORITIES = [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_done' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tugas yang belum selesai dan deadline-nya sudah lewat.
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->where('is_done', false)->whereDate('due_date', '<', today());
    }

    public function isOverdue(): bool
    {
        return ! $this->is_done && $this->due_date->isBefore(today());
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    /**
     * Teks singkat sisa waktu, contoh: "Besok" atau "Terlambat 2 hari".
     */
    public function deadlineLabel(): string
    {
        $days = (int) today()->diffInDays($this->due_date, false);

        return match (true) {
            $days < 0 => 'Terlambat '.abs($days).' hari',
            $days === 0 => 'Hari ini',
            $days === 1 => 'Besok',
            default => $days.' hari lagi',
        };
    }
}
