<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkloadService
{
    public function getUserWorkload(User $user): array
    {
        $now = Carbon::now()->toDateString();

        $activeTasks = $user->assignedTasks()
            ->whereNotIn('status', ['done', 'cancelled'])
            ->get();

        $overdueTasksCount = $activeTasks->where('deadline', '<', $now)->count();
        $totalEstimatedHours = $activeTasks->sum('estimated_hours');
        $totalActualHours = $activeTasks->sum('actual_hours');
        $projectCount = $user->projectMemberships()->where('status', 'active')->count();

        // Evaluate workload category
        $status = 'normal';
        if ($totalEstimatedHours > 45 || $activeTasks->count() >= 8 || $overdueTasksCount >= 2) {
            $status = 'overloaded';
        } elseif ($totalEstimatedHours > 30 || $activeTasks->count() >= 5 || $overdueTasksCount >= 1) {
            $status = 'high';
        }

        return [
            'user' => $user,
            'active_tasks_count' => $activeTasks->count(),
            'overdue_tasks_count' => $overdueTasksCount,
            'estimated_hours' => round($totalEstimatedHours, 1),
            'actual_hours' => round($totalActualHours, 1),
            'project_count' => $projectCount,
            'status' => $status, // normal, high, overloaded
        ];
    }

    public function getAllUsersWorkload(): Collection
    {
        $operationalUsers = User::where('status', 'active')
            ->where('role', '!=', 'viewer')
            ->get();

        return $operationalUsers->map(fn($user) => $this->getUserWorkload($user));
    }
}
