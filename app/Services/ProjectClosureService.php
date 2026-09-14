<?php

namespace App\Services;

use App\Models\Project;
use Carbon\Carbon;

class ProjectClosureService
{
    /**
     * Conduct a full checklist inspection for project closure.
     */
    public function inspectClosureReadiness(Project $project): array
    {
        $incompleteTasksCount = $project->tasks()->where('status', '!=', 'done')->count();
        $unpaidInvoicesCount = $project->invoices()->where('payment_status', '!=', 'paid')->count();
        $unresolvedBlockersCount = $project->blockers()->whereNotIn('status', ['resolved', 'closed'])->count();
        $linkedResourcesCount = $project->resources()->where('status', '!=', 'retired')->count();
        $documentsCount = $project->documents()->count();

        $checklist = [
            [
                'title' => 'All Tasks Completed',
                'passed' => $incompleteTasksCount === 0,
                'detail' => $incompleteTasksCount === 0 ? 'All tasks marked done.' : "{$incompleteTasksCount} incomplete task(s) remaining.",
            ],
            [
                'title' => 'All Invoices Paid',
                'passed' => $unpaidInvoicesCount === 0,
                'detail' => $unpaidInvoicesCount === 0 ? 'No outstanding unpaid invoices.' : "{$unpaidInvoicesCount} invoice(s) pending payment.",
            ],
            [
                'title' => 'All Blockers Resolved',
                'passed' => $unresolvedBlockersCount === 0,
                'detail' => $unresolvedBlockersCount === 0 ? 'Zero active blockers.' : "{$unresolvedBlockersCount} open blocker(s).",
            ],
            [
                'title' => 'Project Documentation Complete',
                'passed' => $documentsCount > 0,
                'detail' => $documentsCount > 0 ? "{$documentsCount} document(s) attached." : 'No closure or technical documentation uploaded.',
            ],
            [
                'title' => 'Resource Review',
                'passed' => true,
                'detail' => "{$linkedResourcesCount} resource(s) allocated to this project.",
            ],
        ];

        $canClose = ($incompleteTasksCount === 0) && ($unresolvedBlockersCount === 0);

        return [
            'can_close' => $canClose,
            'checklist' => $checklist,
            'incomplete_tasks' => $incompleteTasksCount,
            'unpaid_invoices' => $unpaidInvoicesCount,
            'unresolved_blockers' => $unresolvedBlockersCount,
        ];
    }

    /**
     * Officially close project with audit trail.
     */
    public function closeProject(Project $project, string $closureNotes = ''): void
    {
        $project->update([
            'status' => 'completed',
            'health' => 'on_track',
            'closed_at' => Carbon::now(),
            'closure_notes' => $closureNotes,
        ]);

        AuditLogger::log('complete', 'Project', $project->id, null, ['status' => 'completed'], "Project {$project->name} closed successfully: {$closureNotes}");
    }
}
