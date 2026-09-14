<?php

namespace App\Http\Controllers;

use App\Models\Blocker;
use App\Models\DailyProgress;
use App\Models\Project;
use App\Models\Task;
use App\Services\AuditLogger;
use App\Services\ProjectHealthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyProgressController extends Controller
{
    public function __construct(protected ProjectHealthService $healthService) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DailyProgress::with('user', 'project', 'task');

        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $progressList = $query->orderByDesc('date')->orderByDesc('created_at')->paginate(20);
        
        $myProjects = $user->isSuperAdmin() 
            ? Project::where('status', 'active')->get()
            : Project::where(function ($q) use ($user) {
                $q->whereHas('members', fn($m) => $m->where('user_id', $user->id))
                  ->orWhereHas('tasks', fn($t) => $t->where('assignee_id', $user->id));
            })->where('status', 'active')->get();

        $myTasks = Task::where('assignee_id', $user->id)->whereNotIn('status', ['done', 'cancelled'])->get();

        return view('progress.index', compact('progressList', 'myProjects', 'myTasks'));
    }

    public function store(Request $request)
    {
        if (!$request->filled('task_id')) {
            $request->merge(['task_id' => null]);
        }
        if (!$request->filled('blocker')) {
            $request->merge(['blocker' => null]);
        }

        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'date' => 'required|date',
            'progress' => 'required|integer|min:0|max:100',
            'completed_work' => 'required|string',
            'next_plan' => 'required|string',
            'blocker' => 'nullable|string',
            'working_hours' => 'required|numeric|min:0.5|max:24',
        ]);

        $data['user_id'] = Auth::id();

        // 1. Create historical record (never overwrite)
        $daily = DailyProgress::create($data);

        // 2. Update task progress if linked
        if ($daily->task_id) {
            $task = Task::find($daily->task_id);
            if ($task) {
                $task->progress = $daily->progress;
                $task->actual_hours += $daily->working_hours;
                if ($daily->progress >= 100) {
                    $task->status = 'done';
                } elseif ($task->status === 'to_do' || $task->status === 'backlog') {
                    $task->status = 'in_progress';
                }
                $task->save();

                if ($task->milestone) {
                    $task->milestone->recalculateProgress();
                }
            }
        }

        // 3. If blocker text is provided, auto-report a Blocker
        if (!empty($daily->blocker)) {
            Blocker::create([
                'project_id' => $daily->project_id,
                'task_id' => $daily->task_id,
                'title' => 'Blocker: ' . mb_substr($daily->blocker, 0, 80),
                'description' => $daily->blocker,
                'priority' => 'high',
                'type' => 'technical',
                'reporter_id' => Auth::id(),
                'status' => 'open',
            ]);

            if ($daily->task_id) {
                $task = Task::find($daily->task_id);
                if ($task && $task->status !== 'done') {
                    $task->status = 'blocked';
                    $task->save();
                }
            }
        }

        $project = Project::find($daily->project_id);
        if ($project) {
            $this->healthService->evaluateHealth($project);
        }

        AuditLogger::log('create', 'DailyProgress', $daily->id, null, $daily->toArray(), "Daily progress submitted by " . Auth::user()->name);

        return redirect()->route('progress.index')->with('success', 'Daily progress saved to operational timeline.');
    }
}
