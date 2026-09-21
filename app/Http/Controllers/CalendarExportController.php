<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CalendarExportController extends Controller
{
    /**
     * Unduh deadline tugas yang belum selesai sebagai file .ics
     * (bisa diimpor ke Google Calendar, Apple Calendar, Outlook, dll).
     */
    public function __invoke(Request $request): Response
    {
        $tasks = $request->user()->tasks()
            ->with('course')
            ->where('is_done', false)
            ->orderBy('due_date')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//CampusTask//Tugas Kampus//ID',
            'CALSCALE:GREGORIAN',
            'X-WR-CALNAME:Tugas Kampus',
        ];

        foreach ($tasks as $task) {
            array_push(
                $lines,
                'BEGIN:VEVENT',
                "UID:task-{$task->id}@campustask",
                'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
                'DTSTART;VALUE=DATE:'.$task->due_date->format('Ymd'),
                'DTEND;VALUE=DATE:'.$task->due_date->copy()->addDay()->format('Ymd'),
                'SUMMARY:'.$this->escape("{$task->title} ({$task->courseName()})"),
                'DESCRIPTION:'.$this->escape($task->description ?? ''),
                'END:VEVENT',
            );
        }

        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="tugas-kampus.ics"',
        ]);
    }

    /**
     * Karakter khusus di format iCalendar harus di-escape.
     */
    private function escape(string $text): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $text);
    }
}
