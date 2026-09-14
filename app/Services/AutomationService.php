<?php

namespace App\Services;

use App\Models\AutomationLog;
use App\Models\AutomationRule;
use App\Models\DailyProgress;
use App\Models\Infrastructure;
use App\Models\InventoryItem;
use App\Models\Notification;
use App\Models\PurchaseRequest;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutomationService
{
    /**
     * Run all automated active triggers.
     */
    public function runAutomations(): array
    {
        $executed = [];
        $rules = AutomationRule::where('is_active', true)->get();

        foreach ($rules as $rule) {
            try {
                $status = 'success';
                $message = '';

                switch ($rule->trigger_event) {
                    case 'domain_expiring':
                        $message = $this->handleDomainExpiring($rule);
                        break;
                    case 'inventory_low':
                        $message = $this->handleLowInventory($rule);
                        break;
                    case 'daily_progress_missing':
                        $message = $this->handleMissingDailyProgress($rule);
                        break;
                    case 'task_overdue':
                        $message = $this->handleTaskOverdue($rule);
                        break;
                    default:
                        $message = "Trigger {$rule->trigger_event} evaluated without pending triggers.";
                }

                $rule->update(['last_triggered_at' => Carbon::now()]);

                AutomationLog::create([
                    'automation_rule_id' => $rule->id,
                    'trigger_event' => $rule->trigger_event,
                    'status' => 'success',
                    'message' => $message,
                ]);

                $executed[] = ['rule' => $rule->name, 'result' => $message];
            } catch (\Throwable $e) {
                Log::error("Automation error in rule {$rule->id}: " . $e->getMessage());
                AutomationLog::create([
                    'automation_rule_id' => $rule->id,
                    'trigger_event' => $rule->trigger_event,
                    'status' => 'failed',
                    'message' => 'Error: ' . $e->getMessage(),
                ]);
            }
        }

        return $executed;
    }

    private function handleDomainExpiring(AutomationRule $rule): string
    {
        $days = $rule->condition_config['days'] ?? 30;
        $domains = Infrastructure::whereIn('type', ['domain', 'ssl'])
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays($days)])
            ->get();

        $count = 0;
        $superAdmins = User::where('role', 'super_admin')->get();

        foreach ($domains as $domain) {
            foreach ($superAdmins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'automation_domain_expiry',
                    'title' => "[AUTOMATION] Domain/SSL Expiring Soon: {$domain->name}",
                    'message' => "Resource {$domain->name} expires on {$domain->expiry_date->format('d M Y')}.",
                    'action_url' => route('resources.infrastructure'),
                    'level' => 'warning',
                ]);
                $count++;
            }
        }

        return "Generated {$count} alerts for domains expiring in <= {$days} days.";
    }

    private function handleLowInventory(AutomationRule $rule): string
    {
        $lowItems = InventoryItem::whereColumn('current_stock', '<=', 'minimum_stock')->get();
        $count = 0;
        $superAdmins = User::where('role', 'super_admin')->get();

        foreach ($lowItems as $item) {
            foreach ($superAdmins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'automation_low_inventory',
                    'title' => "[AUTOMATION] Low Stock Alert: {$item->item_name}",
                    'message' => "Item {$item->item_name} has only {$item->current_stock} {$item->unit} left (Minimum: {$item->minimum_stock}). Suggested: Create Purchase Request.",
                    'action_url' => route('purchasing.index'),
                    'level' => 'danger',
                ]);
                $count++;
            }
        }

        return "Processed {$lowItems->count()} low-stock inventory items, sent {$count} notifications.";
    }

    private function handleMissingDailyProgress(AutomationRule $rule): string
    {
        $today = Carbon::today()->toDateString();
        // Operational users active in projects
        $users = User::where('role', '!=', 'super_admin')
            ->where('role', '!=', 'viewer')
            ->where('status', 'active')
            ->whereHas('assignedTasks', fn($q) => $q->whereNotIn('status', ['done', 'cancelled']))
            ->get();

        $missingCount = 0;
        foreach ($users as $user) {
            $hasSubmitted = DailyProgress::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->exists();

            if (!$hasSubmitted) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'daily_progress_missing',
                    'title' => "Daily Progress Missing for Today",
                    'message' => "Hi {$user->name}, please submit your daily progress report before logging off.",
                    'action_url' => route('progress.index'),
                    'level' => 'warning',
                ]);
                $missingCount++;
            }
        }

        return "Reminded {$missingCount} users to submit daily progress.";
    }

    private function handleTaskOverdue(AutomationRule $rule): string
    {
        $today = Carbon::today()->toDateString();
        $overdueTasks = Task::whereNotNull('deadline')
            ->where('deadline', '<', $today)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->get();

        foreach ($overdueTasks as $task) {
            if ($task->assignee_id) {
                Notification::create([
                    'user_id' => $task->assignee_id,
                    'type' => 'task_overdue',
                    'title' => "Task Overdue: {$task->title}",
                    'message' => "Task deadline was {$task->deadline->format('d M Y')}. Please update progress or report a blocker.",
                    'action_url' => route('projects.show', $task->project_id),
                    'level' => 'danger',
                ]);
            }
        }

        return "Notified assignees of {$overdueTasks->count()} overdue tasks.";
    }
}
