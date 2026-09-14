<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Comment;
use App\Models\Milestone;
use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Resource;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ProjectClosureService;
use App\Services\ProjectHealthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectHealthService $healthService,
        protected ProjectClosureService $closureService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('project.view')) {
            abort(403, 'Unauthorized to view projects.');
        }

        $query = Project::with('client', 'tasks', 'members.user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('health')) {
            $query->where('health', $request->health);
        }

        $projects = $query->orderByDesc('created_at')->get();
        foreach ($projects as $p) {
            $this->healthService->evaluateHealth($p);
        }

        $clients = Client::where('status', 'active')->get();
        return view('projects.index', compact('projects', 'clients'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) {
            $request->merge([
                'revenue' => 0,
                'budget' => 0,
            ]);
        }

        $data = $request->validate([
            'project_code' => 'required|string|unique:projects,project_code',
            'name' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'description' => 'nullable|string',
            'project_type' => 'required|string',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
            'revenue' => Auth::user()->isSuperAdmin() ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'budget' => Auth::user()->isSuperAdmin() ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
        ]);

        $data['revenue'] = (float) ($data['revenue'] ?? 0);
        $data['budget'] = (float) ($data['budget'] ?? 0);
        $data['actual_cost'] = 0;
        $data['profit'] = $data['revenue'];
        $data['profit_margin'] = $data['revenue'] > 0 ? 100 : 0;
        $data['health'] = 'on_track';

        $project = Project::create($data);

        // Automatically assign creator as project member
        ProjectMember::firstOrCreate(
            ['project_id' => $project->id, 'user_id' => Auth::id()],
            [
                'role' => Auth::user()->role,
                'responsibility' => 'Project Initiator',
                'assigned_date' => Carbon::now()->toDateString(),
                'status' => 'active',
            ]
        );

        AuditLogger::log('create', 'Project', $project->id, null, $project->toArray(), "New project created: {$project->name}");

        return redirect()->route('projects.show', $project->id)->with('success', "Project {$project->name} created successfully.");
    }

    public function show(Project $project, ?Request $request = null)
    {
        $request = $request ?? request();
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->hasPermission('project.view')) {
            abort(403, 'Unauthorized to view this project.');
        }

        $this->healthService->evaluateHealth($project);
        $project->load([
            'client',
            'milestones.tasks.assignee',
            'tasks.assignee',
            'tasks.blockedBy',
            'tasks.blocking',
            'members.user',
            'dailyProgress.user',
            'blockers.reporter',
            'blockers.responsibleUser',
            'incomes',
            'expenses',
            'invoices',
            'resources',
            'documents',
            'comments.user',
        ]);

        $closureEvaluation = $this->closureService->inspectClosureReadiness($project);
        $allUsers = User::where('status', 'active')->get();
        $activeTab = $request->get('tab', 'overview');

        return view('projects.show', compact('project', 'closureEvaluation', 'allUsers', 'activeTab'));
    }

    public function addMember(Request $request, Project $project)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string',
            'responsibility' => 'nullable|string',
        ]);

        $data['project_id'] = $project->id;
        $data['assigned_date'] = Carbon::now()->toDateString();
        $data['status'] = 'active';

        $member = ProjectMember::updateOrCreate(
            ['project_id' => $project->id, 'user_id' => $data['user_id']],
            $data
        );

        Notification::create([
            'user_id' => $data['user_id'],
            'type' => 'project',
            'title' => 'Ditambahkan ke Proyek: ' . $project->name,
            'message' => Auth::user()->name . " menugaskan Anda ke dalam tim proyek '{$project->name}' sebagai {$data['role']}.",
            'action_url' => route('projects.show', $project->id),
            'level' => 'info',
        ]);

        AuditLogger::log('assign', 'ProjectMember', $member->id, null, $member->toArray(), "Assigned user #{$member->user_id} to project {$project->name}");
        return back()->with('success', 'Team member assigned successfully.');
    }

    public function storeMilestone(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $data['project_id'] = $project->id;
        $data['progress'] = 0;

        $milestone = Milestone::create($data);
        AuditLogger::log('create', 'Milestone', $milestone->id, null, $milestone->toArray(), "New milestone created: {$milestone->name}");
        return back()->with('success', "Milestone '{$milestone->name}' added.");
    }

    public function storeTask(Request $request, Project $project)
    {
        foreach (['milestone_id', 'assignee_id', 'blocked_by_task_id', 'start_date', 'deadline', 'description'] as $field) {
            if (!$request->filled($field)) {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'milestone_id' => 'nullable|exists:milestones,id',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:backlog,to_do,in_progress,waiting_review,revision,blocked,done,cancelled',
            'progress' => 'required|integer|min:0|max:100',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'estimated_hours' => 'required|numeric|min:0',
            'blocked_by_task_id' => 'nullable|exists:tasks,id',
        ]);

        $data['project_id'] = $project->id;
        $blockedBy = $data['blocked_by_task_id'] ?? null;
        unset($data['blocked_by_task_id']);

        $task = Task::create($data);

        if ($task->assignee_id) {
            $assignee = User::find($task->assignee_id);
            if ($assignee) {
                ProjectMember::firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $assignee->id],
                    [
                        'role' => $assignee->role,
                        'responsibility' => 'Task Contributor',
                        'assigned_date' => Carbon::now()->toDateString(),
                        'status' => 'active',
                    ]
                );

                Notification::create([
                    'user_id' => $assignee->id,
                    'type' => 'task',
                    'title' => 'Tugas Baru Ditugaskan: ' . $task->title,
                    'message' => Auth::user()->name . " menugaskan tugas '{$task->title}' pada proyek '{$project->name}' kepada Anda.",
                    'action_url' => route('projects.show', $project->id) . '?tab=tasks',
                    'level' => 'info',
                ]);
            }
        }

        if ($blockedBy) {
            TaskDependency::create([
                'task_id' => $task->id,
                'depends_on_task_id' => $blockedBy,
                'type' => 'blocked_by',
            ]);
            $task->checkDependencyStatus();
        }

        if ($task->milestone) {
            $task->milestone->recalculateProgress();
        }

        $this->healthService->evaluateHealth($project);
        AuditLogger::log('create', 'Task', $task->id, null, $task->toArray(), "New task created: {$task->title}");

        return back()->with('success', "Task '{$task->title}' added successfully.");
    }

    public function claimTask(Request $request, Task $task)
    {
        $user = Auth::user();

        // Enforce membership
        ProjectMember::firstOrCreate(
            ['project_id' => $task->project_id, 'user_id' => $user->id],
            [
                'role' => $user->role,
                'responsibility' => 'Task Executor',
                'assigned_date' => Carbon::now()->toDateString(),
                'status' => 'active',
            ]
        );

        $oldAssignee = $task->assignee_id;
        $task->assignee_id = $user->id;
        if (in_array($task->status, ['backlog', 'to_do'])) {
            $task->status = 'in_progress';
        }
        $task->save();

        if ($task->milestone) {
            $task->milestone->recalculateProgress();
        }
        $this->healthService->evaluateHealth($task->project);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'task',
            'title' => 'Tugas Berhasil Diklaim: ' . $task->title,
            'message' => "Anda telah berhasil mengklaim tugas '{$task->title}' pada proyek '{$task->project->name}'. Status tugas kini 'In Progress'.",
            'action_url' => route('projects.show', $task->project_id) . '?tab=tasks',
            'level' => 'success',
        ]);

        AuditLogger::log('claim', 'Task', $task->id, ['assignee_id' => $oldAssignee], ['assignee_id' => $user->id, 'status' => $task->status], "User {$user->name} claimed task #{$task->id} ({$task->title})");

        return back()->with('success', "Tugas '{$task->title}' berhasil diklaim! Silakan mulai pengerjaan.");
    }

    public function updateTaskStatus(Request $request, Task $task)
    {
        $data = $request->validate([
            'status' => 'required|in:backlog,to_do,in_progress,waiting_review,revision,blocked,done,cancelled',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $oldStatus = $task->status;
        $task->status = $data['status'];
        if (isset($data['progress'])) {
            $task->progress = $data['progress'];
        } elseif ($data['status'] === 'done') {
            $task->progress = 100;
        }

        $task->save();

        // If task is done, check tasks that depend on it
        if ($task->status === 'done') {
            foreach ($task->blocking as $dependentTask) {
                $dependentTask->checkDependencyStatus();
            }
        }

        if ($task->milestone) {
            $task->milestone->recalculateProgress();
        }

        $this->healthService->evaluateHealth($task->project);
        AuditLogger::log('update', 'Task', $task->id, ['status' => $oldStatus], ['status' => $task->status], "Task #{$task->id} status updated to {$task->status}");

        return back()->with('success', "Task updated.");
    }

    public function closeProject(Request $request, Project $project)
    {
        $closureNotes = $request->input('closure_notes', 'Project successfully completed and verified.');
        $this->closureService->closeProject($project, $closureNotes);

        return redirect()->route('projects.show', $project->id)->with('success', "Project {$project->name} officially closed.");
    }

    public function storeComment(Request $request, Project $project)
    {
        $data = $request->validate([
            'content' => 'required|string|max:4000',
        ]);

        $mentions = [];
        preg_match_all('/@([\w.\-]+)/', $data['content'], $matches);
        if (!empty($matches[1])) {
            $mentionedUsers = User::where(function ($q) use ($matches) {
                foreach ($matches[1] as $handle) {
                    $q->orWhere('email', 'like', $handle . '%')
                        ->orWhere('name', 'like', $handle . '%');
                }
            })->get();

            foreach ($mentionedUsers as $mentioned) {
                $mentions[] = $mentioned->id;
                \App\Models\Notification::create([
                    'user_id' => $mentioned->id,
                    'type' => 'mention',
                    'title' => Auth::user()->name . ' mentioned you',
                    'message' => mb_substr($data['content'], 0, 180),
                    'action_url' => route('projects.show', $project->id),
                    'level' => 'info',
                ]);
            }
        }

        Comment::create([
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
            'user_id' => Auth::id(),
            'content' => $data['content'],
            'mentions' => $mentions,
        ]);

        AuditLogger::log('create', 'Comment', $project->id, null, ['content' => $data['content']], 'Project comment added');
        return back()->with('success', 'Comment posted.');
    }
}
