<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Session;
use App\Helpers\View;
use App\Models\Notification;
use App\Models\Task;

class CalendarController
{
    public function index(): void
    {
        $userId = Session::userId();

        $year = (int) ($_GET['year'] ?? date('Y'));
        $month = (int) ($_GET['month'] ?? date('n'));

        // Bound validation
        if ($month < 1) {
            $month = 12;
            $year--;
        } elseif ($month > 12) {
            $month = 1;
            $year++;
        }

        $currentMonthTimestamp = mktime(0, 0, 0, $month, 1, $year);
        $monthName = date('F', $currentMonthTimestamp);
        $daysInMonth = (int) date('t', $currentMonthTimestamp);
        $firstDayOfWeek = (int) date('w', $currentMonthTimestamp); // 0 = Sunday

        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = sprintf('%04d-%02d-%02d', $year, $month, $daysInMonth);

        $tasks = Task::getTasksForCalendar($userId, $startDate, $endDate);

        // Group tasks by date string (YYYY-MM-DD)
        $tasksByDate = [];
        foreach ($tasks as $task) {
            $d = $task['due_date'];
            $tasksByDate[$d][] = $task;
        }

        // Previous and next month links
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $unreadCount = Notification::getUnreadCount($userId);

        View::render('calendar.index', [
            'pageTitle' => "Calendar: {$monthName} {$year} — PRAGATHI",
            'activeNav' => 'calendar',
            'year' => $year,
            'month' => $month,
            'monthName' => $monthName,
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'tasksByDate' => $tasksByDate,
            'prevYear' => $prevYear,
            'prevMonth' => $prevMonth,
            'nextYear' => $nextYear,
            'nextMonth' => $nextMonth,
            'todayDate' => date('Y-m-d'),
            'unreadNotificationCount' => $unreadCount,
        ]);
    }
}
