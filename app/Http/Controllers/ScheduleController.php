<?php

namespace App\Http\Controllers;

use App\Services\ReminderService;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService,
        protected ReminderService $reminderService
    ) {}

    public function index(?Request $request = null)
    {
        $request = $request ?? request();
        $user = Auth::user();
        
        $month = (int) $request->get('month', Carbon::now()->month);
        $year = (int) $request->get('year', Carbon::now()->year);
        $currentDate = Carbon::createFromDate($year, $month, 1);

        $prevMonthDate = $currentDate->copy()->subMonth();
        $nextMonthDate = $currentDate->copy()->addMonth();

        $events = $this->scheduleService->getEvents($user);

        // Filter events by month
        $monthEvents = $events->filter(function ($e) use ($month, $year) {
            $d = Carbon::parse($e['date']);
            return $d->month === $month && $d->year === $year;
        });

        // Group events by day of month (e.g. 1, 2, ..., 31)
        $eventsByDay = $monthEvents->groupBy(function ($e) {
            return (int) Carbon::parse($e['date'])->format('j');
        });

        // Calendar grid calculations
        $daysInMonth = $currentDate->daysInMonth;
        $startDayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

        // Agenda Grouped
        $agendaGrouped = $events->groupBy('date')->sortKeys();

        return view('schedule.index', compact(
            'events',
            'monthEvents',
            'eventsByDay',
            'agendaGrouped',
            'currentDate',
            'prevMonthDate',
            'nextMonthDate',
            'daysInMonth',
            'startDayOfWeek',
            'month',
            'year'
        ));
    }

    public function triggerReminders()
    {
        $count = $this->reminderService->scanAndGenerateReminders();
        return back()->with('success', "Schedule Engine: {$count} reminder notifications generated & dispatched.");
    }
}
