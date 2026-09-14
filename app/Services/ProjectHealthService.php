<?php

namespace App\Services;

use App\Models\CompanyProfile;
use App\Models\Project;
use Carbon\Carbon;

class ProjectHealthService
{
    /**
     * Calculate and update project health status.
     * Health statuses: on_track, at_risk, off_track
     */
    public function evaluateHealth(Project $project): array
    {
        $now = Carbon::now();
        $startDate = Carbon::parse($project->start_date);
        $deadline = Carbon::parse($project->deadline);

        $totalDays = max(1, $startDate->diffInDays($deadline));
        $daysElapsed = max(0, $startDate->diffInDays($now));
        $timeElapsedPercent = min(100, round(($daysElapsed / $totalDays) * 100));

        // Average progress of tasks or milestones
        $actualProgress = (int) round($project->tasks()->avg('progress') ?? 0);

        // Count metrics
        $overdueTasksCount = $project->tasks()
            ->where('status', '!=', 'done')
            ->where('deadline', '<', $now->toDateString())
            ->count();

        $blockedTasksCount = $project->tasks()
            ->where('status', 'blocked')
            ->count();

        $openBlockersCount = $project->blockers()
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        // Calculate progress gap
        $progressGap = $timeElapsedPercent - $actualProgress;

        $thresholds = CompanyProfile::query()->value('settings') ?? [];
        if (is_string($thresholds)) {
            $thresholds = json_decode($thresholds, true) ?? [];
        }
        $healthThresholds = $thresholds['project_health_thresholds'] ?? $thresholds['health'] ?? [];
        $overdueOffTrack = (int) ($healthThresholds['overdue_tasks_off_track'] ?? 3);
        $gapOffTrack = (int) ($healthThresholds['progress_gap_off_track'] ?? 30);
        $gapAtRisk = (int) ($healthThresholds['progress_gap_at_risk'] ?? 15);

        // Health decision logic
        $health = 'on_track';
        $reasons = [];

        if ($overdueTasksCount >= $overdueOffTrack || $openBlockersCount >= 3 || $progressGap > $gapOffTrack || ($now->gt($deadline) && $actualProgress < 100)) {
            $health = 'off_track';
            if ($now->gt($deadline) && $actualProgress < 100) $reasons[] = 'Project has passed deadline with incomplete tasks.';
            if ($overdueTasksCount >= $overdueOffTrack) $reasons[] = "{$overdueTasksCount} overdue tasks.";
            if ($openBlockersCount >= 3) $reasons[] = "{$openBlockersCount} unresolved blockers.";
            if ($progressGap > $gapOffTrack) $reasons[] = "Progress lag of {$progressGap}% compared to timeline.";
        } elseif ($overdueTasksCount >= 1 || $openBlockersCount >= 1 || $blockedTasksCount >= 2 || $progressGap > $gapAtRisk) {
            $health = 'at_risk';
            if ($overdueTasksCount >= 1) $reasons[] = "{$overdueTasksCount} task(s) overdue.";
            if ($openBlockersCount >= 1) $reasons[] = "{$openBlockersCount} active blocker(s).";
            if ($blockedTasksCount >= 2) $reasons[] = "{$blockedTasksCount} blocked task(s).";
            if ($progressGap > 15) $reasons[] = "Progress lag of {$progressGap}%.";
        } else {
            $reasons[] = 'All tasks progressing within timeline thresholds.';
        }

        $project->health = $health;
        $project->save();

        return [
            'health' => $health,
            'actual_progress' => $actualProgress,
            'time_elapsed_percent' => $timeElapsedPercent,
            'overdue_tasks' => $overdueTasksCount,
            'blocked_tasks' => $blockedTasksCount,
            'open_blockers' => $openBlockersCount,
            'reasons' => $reasons,
        ];
    }
}
