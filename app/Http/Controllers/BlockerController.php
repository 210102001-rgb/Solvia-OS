<?php

namespace App\Http\Controllers;

use App\Models\Blocker;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ProjectHealthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockerController extends Controller
{
    public function __construct(protected ProjectHealthService $healthService) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Blocker::with('project', 'task', 'reporter', 'responsibleUser');

        if (!$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('reporter_id', $user->id)
                  ->orWhere('responsible_user_id', $user->id)
                  ->orWhereHas('project.members', fn($pm) => $pm->where('user_id', $user->id));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $blockers = $query->orderByDesc('created_at')->paginate(20);
        $projects = Project::where('status', 'active')->get();
        $users = User::where('status', 'active')->get();

        return view('blockers.index', compact('blockers', 'projects', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'type' => 'required|in:technical,resource,dependency,client,other',
            'responsible_user_id' => 'nullable|exists:users,id',
        ]);

        $data['reporter_id'] = Auth::id();
        $data['status'] = 'open';

        $blocker = Blocker::create($data);

        // Auto mark task as blocked
        if ($blocker->task_id) {
            $task = Task::find($blocker->task_id);
            if ($task && $task->status !== 'done') {
                $task->status = 'blocked';
                $task->save();
            }
        }

        $project = Project::find($blocker->project_id);
        if ($project) {
            $this->healthService->evaluateHealth($project);
        }

        AuditLogger::log('create', 'Blocker', $blocker->id, null, $blocker->toArray(), "New blocker reported on project #{$blocker->project_id}: {$blocker->title}");

        return back()->with('success', 'Blocker reported. Project health updated.');
    }

    public function resolve(Request $request, Blocker $blocker)
    {
        $data = $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $blocker->update([
            'status' => 'resolved',
            'resolved_date' => Carbon::now(),
            'resolution_notes' => $data['resolution_notes'],
        ]);

        // If linked task was blocked, unblock it
        if ($blocker->task_id) {
            $task = Task::find($blocker->task_id);
            if ($task && $task->status === 'blocked') {
                $task->status = 'in_progress';
                $task->save();
            }
        }

        if ($blocker->project) {
            $this->healthService->evaluateHealth($blocker->project);
        }

        AuditLogger::log('update', 'Blocker', $blocker->id, null, ['status' => 'resolved'], "Blocker #{$blocker->id} resolved");

        return back()->with('success', 'Blocker marked as resolved.');
    }
}
