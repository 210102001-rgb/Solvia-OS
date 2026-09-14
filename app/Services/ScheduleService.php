<?php

namespace App\Services;

use App\Models\AssetMaintenance;
use App\Models\Contract;
use App\Models\DailyProgress;
use App\Models\Infrastructure;
use App\Models\Invoice;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\User;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ScheduleService
{
    /**
     * Pure Schedule Engine reading directly from origin entity dates without duplication.
     */
    public function getEvents(?User $user = null, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $events = collect();
        $isSuperAdmin = $user ? $user->isSuperAdmin() : true;

        // 1. Task Deadlines
        $tasksQuery = Task::with('project', 'assignee')->whereNotNull('deadline');
        if ($user && !$isSuperAdmin) {
            $tasksQuery->where('assignee_id', $user->id);
        }
        if ($startDate && $endDate) {
            $tasksQuery->whereBetween('deadline', [$startDate->toDateString(), $endDate->toDateString()]);
        }
        foreach ($tasksQuery->get() as $task) {
            $events->push([
                'id' => 'task-' . $task->id,
                'title' => 'Task Due: ' . $task->title,
                'date' => $task->deadline->toDateString(),
                'category' => 'Task Deadline',
                'entity_type' => 'Task',
                'entity_id' => $task->id,
                'status' => $task->status,
                'badge_color' => $task->status === 'done' ? 'emerald' : ($task->deadline->isPast() ? 'rose' : 'blue'),
                'context' => $task->project ? $task->project->name : 'General',
                'url' => route('projects.show', $task->project_id),
            ]);
        }

        // 2. Project Deadlines
        $projectsQuery = Project::whereNotNull('deadline');
        if ($user && !$isSuperAdmin) {
            $projectsQuery->whereHas('members', fn($q) => $q->where('user_id', $user->id));
        }
        if ($startDate && $endDate) {
            $projectsQuery->whereBetween('deadline', [$startDate->toDateString(), $endDate->toDateString()]);
        }
        foreach ($projectsQuery->get() as $project) {
            $events->push([
                'id' => 'project-' . $project->id,
                'title' => 'Project Deadline: ' . $project->name,
                'date' => $project->deadline->toDateString(),
                'category' => 'Project Deadline',
                'entity_type' => 'Project',
                'entity_id' => $project->id,
                'status' => $project->status,
                'badge_color' => $project->health === 'off_track' ? 'rose' : ($project->health === 'at_risk' ? 'amber' : 'indigo'),
                'context' => $project->client ? $project->client->name : 'Internal',
                'url' => route('projects.show', $project->id),
            ]);
        }

        // 3. Daily Progress Submissions
        $dailyQuery = DailyProgress::with('user', 'project', 'task')->whereNotNull('date');
        if ($user && !$isSuperAdmin) {
            $dailyQuery->where('user_id', $user->id);
        }
        if ($startDate && $endDate) {
            $dailyQuery->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
        }
        foreach ($dailyQuery->get() as $dp) {
            $userName = $dp->user ? $dp->user->name : 'Member';
            $projName = $dp->project ? $dp->project->name : ($dp->task ? $dp->task->title : 'General');
            $workSnippet = $dp->completed_work ? Str::limit($dp->completed_work, 35) : 'Progress logged';
            $events->push([
                'id' => 'daily-' . $dp->id,
                'title' => 'Daily: ' . $userName . ' (' . ($dp->progress ?? 0) . '%) - ' . $workSnippet,
                'date' => $dp->date->toDateString(),
                'category' => 'Daily Progress',
                'entity_type' => 'DailyProgress',
                'entity_id' => $dp->id,
                'status' => ($dp->progress >= 100) ? 'completed' : 'submitted',
                'badge_color' => 'emerald',
                'context' => $projName . ($dp->working_hours ? ' • ' . $dp->working_hours . 'h' : ''),
                'url' => route('progress.index'),
            ]);
        }

        // 4. Milestone Deadlines
        $milestonesQuery = Milestone::with('project')->whereNotNull('deadline');
        if ($user && !$isSuperAdmin) {
            $milestonesQuery->whereHas('project.members', fn($q) => $q->where('user_id', $user->id));
        }
        if ($startDate && $endDate) {
            $milestonesQuery->whereBetween('deadline', [$startDate->toDateString(), $endDate->toDateString()]);
        }
        foreach ($milestonesQuery->get() as $milestone) {
            $events->push([
                'id' => 'milestone-' . $milestone->id,
                'title' => 'Milestone: ' . $milestone->name . ' (' . ($milestone->progress ?? 0) . '%)',
                'date' => $milestone->deadline->toDateString(),
                'category' => 'Milestone Deadline',
                'entity_type' => 'Milestone',
                'entity_id' => $milestone->id,
                'status' => $milestone->status,
                'badge_color' => $milestone->status === 'completed' ? 'emerald' : ($milestone->deadline->isPast() ? 'rose' : 'indigo'),
                'context' => $milestone->project ? $milestone->project->name : 'General',
                'url' => route('projects.show', $milestone->project_id),
            ]);
        }

        // Super Admin only company-wide schedules
        if ($isSuperAdmin) {
            // 3. Invoice Due Dates
            $invoices = Invoice::with('client')
                ->where('payment_status', '!=', 'paid')
                ->whereNotNull('due_date')
                ->get();
            foreach ($invoices as $inv) {
                $events->push([
                    'id' => 'invoice-' . $inv->id,
                    'title' => 'Invoice Due: ' . $inv->invoice_number . ' (Rp ' . number_format($inv->total, 0, ',', '.') . ')',
                    'date' => $inv->due_date->toDateString(),
                    'category' => 'Invoice Due',
                    'entity_type' => 'Invoice',
                    'entity_id' => $inv->id,
                    'status' => $inv->payment_status,
                    'badge_color' => $inv->due_date->isPast() ? 'rose' : 'orange',
                    'context' => $inv->client ? $inv->client->name : '',
                    'url' => route('finance.invoices'),
                ]);
            }

            // 4. Infrastructure Expiry (Domains, SSL, VPS Billing)
            $infrastructures = Infrastructure::where(function ($q) {
                $q->whereNotNull('expiry_date')->orWhereNotNull('next_billing_date');
            })->get();
            foreach ($infrastructures as $infra) {
                $date = $infra->expiry_date ?? $infra->next_billing_date;
                $events->push([
                    'id' => 'infra-' . $infra->id,
                    'title' => strtoupper($infra->type) . ' Renewal: ' . $infra->name,
                    'date' => $date->toDateString(),
                    'category' => 'Infrastructure ' . ucfirst($infra->type),
                    'entity_type' => 'Infrastructure',
                    'entity_id' => $infra->id,
                    'status' => $infra->status,
                    'badge_color' => 'purple',
                    'context' => $infra->provider,
                    'url' => route('resources.infrastructure'),
                ]);
            }

            // 5. Subscription Renewals
            $subscriptions = Subscription::whereNotNull('next_billing_date')->get();
            foreach ($subscriptions as $sub) {
                $events->push([
                    'id' => 'sub-' . $sub->id,
                    'title' => 'Subscription: ' . $sub->provider . ' (' . $sub->plan_name . ')',
                    'date' => $sub->next_billing_date->toDateString(),
                    'category' => 'Subscription Billing',
                    'entity_type' => 'Subscription',
                    'entity_id' => $sub->id,
                    'status' => $sub->status,
                    'badge_color' => 'teal',
                    'context' => 'Rp ' . number_format($sub->cost, 0, ',', '.'),
                    'url' => route('resources.subscriptions'),
                ]);
            }

            // 6. Warranties Expiry
            $warranties = Warranty::with('asset')->whereNotNull('expiry_date')->get();
            foreach ($warranties as $warr) {
                $events->push([
                    'id' => 'warranty-' . $warr->id,
                    'title' => 'Warranty Expiry: ' . ($warr->asset ? $warr->asset->brand . ' ' . $warr->asset->model : 'Asset'),
                    'date' => $warr->expiry_date->toDateString(),
                    'category' => 'Warranty Expiry',
                    'entity_type' => 'Warranty',
                    'entity_id' => $warr->id,
                    'status' => 'active',
                    'badge_color' => 'amber',
                    'context' => $warr->provider,
                    'url' => route('resources.assets'),
                ]);
            }

            // 7. Asset Maintenances
            $maintenances = AssetMaintenance::with('asset')->where('status', '!=', 'completed')->get();
            foreach ($maintenances as $maint) {
                $events->push([
                    'id' => 'maintenance-' . $maint->id,
                    'title' => 'Maintenance: ' . ($maint->asset ? $maint->asset->asset_tag : '') . ' (' . $maint->maintenance_type . ')',
                    'date' => $maint->scheduled_date->toDateString(),
                    'category' => 'Asset Maintenance',
                    'entity_type' => 'AssetMaintenance',
                    'entity_id' => $maint->id,
                    'status' => $maint->status,
                    'badge_color' => 'sky',
                    'context' => $maint->technician_vendor ?? 'Technician',
                    'url' => route('resources.assets'),
                ]);
            }

            // 8. Contract Expiries
            $contracts = Contract::whereNotNull('expiry_date')->where('status', 'active')->get();
            foreach ($contracts as $contract) {
                $events->push([
                    'id' => 'contract-' . $contract->id,
                    'title' => 'Contract Expiry: ' . $contract->contract_number . ' (' . $contract->party_name . ')',
                    'date' => $contract->expiry_date->toDateString(),
                    'category' => 'Contract Expiry',
                    'entity_type' => 'Contract',
                    'entity_id' => $contract->id,
                    'status' => $contract->status,
                    'badge_color' => 'indigo',
                    'context' => 'Rp ' . number_format($contract->contract_value, 0, ',', '.'),
                    'url' => route('resources.contracts'),
                ]);
            }
        }

        return $events->sortBy('date')->values();
    }
}
