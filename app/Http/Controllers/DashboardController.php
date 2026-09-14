<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Blocker;
use App\Models\DailyProgress;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Infrastructure;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\Subscription;
use App\Models\Task;
use App\Models\User;
use App\Models\Warranty;
use App\Services\ProjectHealthService;
use App\Services\ScheduleService;
use App\Services\WorkloadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        protected ProjectHealthService $healthService,
        protected WorkloadService $workloadService,
        protected ScheduleService $scheduleService
    ) {}

    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard();
        }

        return $this->operationalDashboard($user);
    }

    protected function superAdminDashboard()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $in30Days = $now->copy()->addDays(30)->toDateString();

        // 1. Financial Overview
        $totalRevenue = (float) Income::sum('amount');
        $totalExpense = (float) Expense::sum('amount');
        $netProfit = $totalRevenue - $totalExpense;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // 2. Project Health
        $projects = Project::with('client', 'tasks')->where('status', '!=', 'completed')->get();
        foreach ($projects as $project) {
            $this->healthService->evaluateHealth($project);
        }
        $activeProjectsCount = $projects->where('status', 'active')->count();
        $atRiskProjectsCount = $projects->where('health', 'at_risk')->count();
        $offTrackProjectsCount = $projects->where('health', 'off_track')->count();
        $onTrackProjectsCount = $projects->where('health', 'on_track')->count();

        // 3. Team Workload
        $teamWorkloads = $this->workloadService->getAllUsersWorkload();
        $overloadedCount = $teamWorkloads->where('status', 'overloaded')->count();
        $highLoadCount = $teamWorkloads->where('status', 'high')->count();

        // 4. Financial Health (Invoices)
        $overdueInvoices = Invoice::with('client')
            ->where('payment_status', '!=', 'paid')
            ->where('due_date', '<', $today)
            ->get();
        $pendingInvoiceAmount = (float) Invoice::where('payment_status', '!=', 'paid')->sum('total');

        // 5. Infrastructure & Subscriptions Expiring Soon (<= 30 days)
        $expiringInfra = Infrastructure::where(function ($q) use ($today, $in30Days) {
            $q->whereBetween('expiry_date', [$today, $in30Days])
              ->orWhereBetween('next_billing_date', [$today, $in30Days]);
        })->get();

        $expiringSubscriptions = Subscription::whereBetween('next_billing_date', [$today, $in30Days])->get();

        // 6. Asset Maintenance & Warranty
        $activeMaintenances = AssetMaintenance::with('asset')->whereIn('status', ['scheduled', 'in_progress'])->get();
        $expiringWarranties = Warranty::with('asset')->whereBetween('expiry_date', [$today, $in30Days])->get();

        // 7. Action Required Items
        $pendingApprovals = PurchaseRequest::with('requester')->where('status', 'submitted')->get();
        $openBlockers = Blocker::with('project', 'reporter')->whereIn('status', ['open', 'in_progress'])->get();
        
        // Missing daily progress for today
        $activeExecutors = User::where('role', '!=', 'super_admin')
            ->where('role', '!=', 'viewer')
            ->where('status', 'active')
            ->get();
        $submittedTodayUserIds = DailyProgress::whereDate('date', $today)->pluck('user_id')->toArray();
        $missingProgressUsers = $activeExecutors->whereNotIn('id', $submittedTodayUserIds);

        // 8. Upcoming Unified Schedule
        $upcomingEvents = $this->scheduleService->getEvents(Auth::user(), $now, $now->copy()->addDays(14))->take(7);

        return view('dashboard.super-admin', compact(
            'totalRevenue',
            'totalExpense',
            'netProfit',
            'profitMargin',
            'projects',
            'activeProjectsCount',
            'atRiskProjectsCount',
            'offTrackProjectsCount',
            'onTrackProjectsCount',
            'teamWorkloads',
            'overloadedCount',
            'highLoadCount',
            'overdueInvoices',
            'pendingInvoiceAmount',
            'expiringInfra',
            'expiringSubscriptions',
            'activeMaintenances',
            'expiringWarranties',
            'pendingApprovals',
            'openBlockers',
            'missingProgressUsers',
            'upcomingEvents'
        ));
    }

    protected function operationalDashboard(User $user)
    {
        $today = Carbon::today()->toDateString();

        // 1. My Projects
        $myProjects = Project::where(function ($q) use ($user) {
            $q->whereHas('members', fn($m) => $m->where('user_id', $user->id))
              ->orWhereHas('tasks', fn($t) => $t->where('assignee_id', $user->id));
        })->with('client')->get();

        // 2. My Tasks
        $myTasks = Task::with('project', 'milestone')
            ->where('assignee_id', $user->id)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('deadline')
            ->get();

        // 2b. Tasks available to claim (unassigned)
        $claimableTasks = Task::with('project', 'milestone')
            ->whereNull('assignee_id')
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('deadline')
            ->take(6)
            ->get();

        $todayTasks = $myTasks->where('deadline', '<=', $today);

        // 3. Daily Progress check
        $hasSubmittedToday = DailyProgress::where('user_id', $user->id)->whereDate('date', $today)->exists();
        $myRecentProgress = DailyProgress::with('project', 'task')
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->take(5)
            ->get();

        // 4. My Reported Blockers
        $myBlockers = Blocker::with('project', 'task')
            ->where('reporter_id', $user->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->get();

        // 5. Assigned Assets
        $myAssets = Asset::with('resource')
            ->where('current_holder_id', $user->id)
            ->get();

        // 6. Announcements
        $announcements = Announcement::where('status', 'published')
            ->where(function ($q) use ($user) {
                $q->where('audience_type', 'all')
                  ->orWhere(function ($sub) use ($user) {
                      $sub->where('audience_type', 'role')->where('audience_target', $user->role);
                  })
                  ->orWhere(function ($sub) use ($user) {
                      $sub->where('audience_type', 'team')->where('audience_target', (string) $user->team_id);
                  });
            })
            ->orderByDesc('publish_date')
            ->take(5)
            ->get();

        // 7. My Upcoming Schedule
        $upcomingEvents = $this->scheduleService->getEvents($user, Carbon::now(), Carbon::now()->addDays(14))->take(7);

        return view('dashboard.operational', compact(
            'user',
            'myProjects',
            'myTasks',
            'todayTasks',
            'hasSubmittedToday',
            'myRecentProgress',
            'myBlockers',
            'myAssets',
            'announcements',
            'upcomingEvents',
            'claimableTasks'
        ));
    }
}
