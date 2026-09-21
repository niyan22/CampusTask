<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
        });

        // Pindahkan nama mata kuliah lama (teks) menjadi baris di tabel courses.
        $colors = ['#A230A4', '#2F80D0', '#2E9C6B', '#E67A2E', '#D6457B', '#7C6FD0'];

        DB::table('tasks')->select('user_id', 'course')->distinct()->get()->each(function ($row, $index) use ($colors) {
            $courseId = DB::table('courses')->insertGetId([
                'user_id' => $row->user_id,
                'name' => $row->course,
                'color' => $colors[$index % count($colors)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tasks')
                ->where('user_id', $row->user_id)
                ->where('course', $row->course)
                ->update(['course_id' => $courseId]);
        });

        // Tugas yang sudah selesai dianggap selesai pada terakhir kali diubah.
        DB::table('tasks')->where('is_done', true)->update(['completed_at' => DB::raw('updated_at')]);

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('course');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('course')->default('');
        });

        DB::table('tasks')
            ->join('courses', 'courses.id', '=', 'tasks.course_id')
            ->select('tasks.id', 'courses.name')
            ->get()
            ->each(fn ($row) => DB::table('tasks')->where('id', $row->id)->update(['course' => $row->name]));

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
            $table->dropConstrainedForeignId('source_task_id');
            $table->dropColumn('completed_at');
        });
    }
};
