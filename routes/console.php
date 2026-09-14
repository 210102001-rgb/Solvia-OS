<?php

use App\Models\Project;
use App\Services\AutomationService;
use App\Services\ProjectHealthService;
use App\Services\ReminderService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('os:run-engines {--only=all}', function () {
    $only = $this->option('only');

    if (in_array($only, ['all', 'reminders'], true)) {
        $count = app(ReminderService::class)->scanAndGenerateReminders();
        $this->info("Reminders generated: {$count}");
    }

    if (in_array($only, ['all', 'automation'], true)) {
        $executed = app(AutomationService::class)->runAutomations();
        $this->info('Automation rules executed: ' . count($executed));
        foreach ($executed as $row) {
            $this->line("- {$row['rule']}: {$row['result']}");
        }
    }

    if (in_array($only, ['all', 'health'], true)) {
        $service = app(ProjectHealthService::class);
        $projects = Project::whereNotIn('status', ['completed', 'cancelled'])->get();
        foreach ($projects as $project) {
            $result = $service->evaluateHealth($project);
            $this->line("{$project->project_code} → {$result['health']}");
        }
    }
})->purpose('Run Solvia.Nova reminder, automation, and project health engines');

Schedule::command('os:run-engines --only=reminders')->dailyAt('07:00');
Schedule::command('os:run-engines --only=health')->hourly();
Schedule::command('os:run-engines --only=automation')->hourly();
