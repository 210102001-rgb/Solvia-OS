<?php

namespace App\Services;

use App\Models\Infrastructure;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\User;
use App\Models\Warranty;
use Carbon\Carbon;

class ReminderService
{
    /**
     * Run daily reminder scan for H-30, H-14, H-7, H-3, H-1, Due Date, H+1.
     */
    public function scanAndGenerateReminders(): int
    {
        $today = Carbon::today();
        $generatedCount = 0;
        $superAdmins = User::where('role', 'super_admin')->get();

        // 1. Task Reminders (For Assignees)
        $tasks = Task::with('assignee', 'project')
            ->whereNotNull('deadline')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->get();

        foreach ($tasks as $task) {
            $daysDiff = $today->diffInDays($task->deadline, false);
            // $daysDiff > 0: days remaining. $daysDiff < 0: overdue.
            if ($task->assignee_id && in_array($daysDiff, [3, 1, 0, -1], true)) {
                $level = $daysDiff < 0 ? 'danger' : ($daysDiff <= 1 ? 'warning' : 'info');
                $statusText = $daysDiff < 0 ? 'Overdue by ' . abs($daysDiff) . ' day(s)' : ($daysDiff === 0 ? 'Due Today' : "Due in {$daysDiff} day(s)");
                
                $title = "Task Reminder: {$task->title}";
                $message = "Your task '{$task->title}' on project '" . ($task->project?->name ?? 'Unknown') . "' is {$statusText}.";

                if ($this->createNotificationIfNotExists($task->assignee_id, 'task_reminder', $title, $message, route('projects.show', $task->project_id), $level)) {
                    $generatedCount++;
                }
            }
        }

        // 2. Invoice Due Reminders (For Super Admins)
        $invoices = Invoice::with('client')
            ->whereNotNull('due_date')
            ->whereNotIn('payment_status', ['paid', 'cancelled'])
            ->get();

        foreach ($invoices as $invoice) {
            $daysDiff = $today->diffInDays($invoice->due_date, false);
            if (in_array($daysDiff, [7, 3, 1, 0, -1, -7], true)) {
                $level = $daysDiff <= 0 ? 'danger' : 'warning';
                $statusText = $daysDiff < 0 ? 'OVERDUE by ' . abs($daysDiff) . ' days' : ($daysDiff === 0 ? 'DUE TODAY' : "Due in {$daysDiff} days");
                $title = "Invoice {$statusText}: {$invoice->invoice_number}";
                $message = "Invoice {$invoice->invoice_number} for " . ($invoice->client?->name ?? 'Client') . " of Rp " . number_format($invoice->total, 0, ',', '.') . " is {$statusText}.";

                foreach ($superAdmins as $admin) {
                    if ($this->createNotificationIfNotExists($admin->id, 'invoice_reminder', $title, $message, route('finance.invoices'), $level)) {
                        $generatedCount++;
                    }
                }
            }
        }

        // 3. Infrastructure & Domain Expiry Reminders (For Super Admins)
        $infrastructures = Infrastructure::where(function ($q) {
            $q->whereNotNull('expiry_date')->orWhereNotNull('next_billing_date');
        })->get();

        foreach ($infrastructures as $infra) {
            $date = $infra->expiry_date ?? $infra->next_billing_date;
            $daysDiff = $today->diffInDays($date, false);

            if (in_array($daysDiff, [30, 14, 7, 3, 1, 0], true)) {
                $level = $daysDiff <= 3 ? 'danger' : 'warning';
                $title = strtoupper($infra->type) . " Expiring in {$daysDiff} days: {$infra->name}";
                $message = "Your {$infra->type} resource '{$infra->name}' ({$infra->provider}) expires/renews on {$date->format('d M Y')}.";

                foreach ($superAdmins as $admin) {
                    if ($this->createNotificationIfNotExists($admin->id, 'infrastructure_expiry', $title, $message, route('resources.infrastructure'), $level)) {
                        $generatedCount++;
                    }
                }
            }
        }

        return $generatedCount;
    }

    private function createNotificationIfNotExists(int $userId, string $type, string $title, string $message, string $actionUrl, string $level): bool
    {
        $today = Carbon::today()->toDateString();
        $exists = Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where('title', $title)
            ->whereDate('created_at', $today)
            ->exists();

        if (!$exists) {
            Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'level' => $level,
                'is_read' => false,
            ]);
            return true;
        }

        return false;
    }
}
