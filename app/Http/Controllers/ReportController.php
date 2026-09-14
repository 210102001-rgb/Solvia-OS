<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Infrastructure;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use App\Services\WorkloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct(protected WorkloadService $workloadService) {}

    public function index(Request $request)
    {
        $reportType = $request->get('type', 'project_summary');
        $dateRange  = [
            'from' => $request->get('from_date', ''),
            'to'   => $request->get('to_date', ''),
        ];

        $data = collect();

        switch ($reportType) {
            case 'financial_summary':
                // Group income+expense by month
                $incomes  = \App\Models\Income::selectRaw("DATE_FORMAT(date,'%Y-%m') as month, SUM(amount) as income")->groupBy('month')->pluck('income','month');
                $expenses = \App\Models\Expense::selectRaw("DATE_FORMAT(date,'%Y-%m') as month, SUM(amount) as expense")->groupBy('month')->pluck('expense','month');
                $months   = collect($incomes->keys()->merge($expenses->keys())->unique()->sort()->values());
                $data     = $months->map(fn($m) => [
                    'month'   => \Carbon\Carbon::parse($m.'-01')->format('M Y'),
                    'income'  => (float)($incomes[$m] ?? 0),
                    'expense' => (float)($expenses[$m] ?? 0),
                ]);
                break;

            case 'team_performance':
                $data = \App\Models\User::where('role', '!=', 'super_admin')
                    ->where('status', 'active')
                    ->withCount(['projectMemberships as projects_count' => fn($q) => $q->where('status','active')])
                    ->withCount(['assignedTasks as tasks_completed' => fn($q) => $q->where('status','done')])
                    ->withCount(['reportedBlockers as blockers_reported'])
                    ->get()
                    ->map(fn($u) => (object)[
                        'name'              => $u->name,
                        'projects_count'    => $u->projects_count,
                        'tasks_completed'   => $u->tasks_completed,
                        'avg_progress'      => (int)round($u->assignedTasks()->avg('progress') ?? 0),
                        'blockers_reported' => $u->blockers_reported,
                    ]);
                break;

            case 'resource_utilization':
                $data = [
                    'total_infra'   => (float)\App\Models\Infrastructure::sum('monthly_cost'),
                    'total_subs'    => (float)\App\Models\Subscription::sum('cost'),
                    'active_assets' => \App\Models\Asset::whereIn('lifecycle_status',['in_use','assigned'])->count(),
                    'items'         => \App\Models\Resource::with('responsibleUser')->orderBy('category')->get()->map(fn($r) => (object)[
                        'type'        => $r->category,
                        'name'        => $r->name,
                        'assigned_to' => $r->responsibleUser ? $r->responsibleUser->name : 'Unassigned',
                        'cost'        => $r->cost ?? 0,
                    ]),
                ];
                break;

            case 'project_summary':
            default:
                $reportType = 'project_summary';
                $data = \App\Models\Project::with('client', 'tasks')->get()->map(fn($p) => (object)[
                    'name'     => $p->name,
                    'health'   => $p->health,
                    'progress' => (int)round($p->tasks->avg('progress') ?? 0),
                    'revenue'  => $p->revenue,
                    'expenses' => $p->actual_cost,
                ]);
                break;
        }

        return view('reports.index', compact('reportType', 'data', 'dateRange'));
    }

    public function exportCsv(Request $request)
    {
        $type = $request->get('type', 'projects');
        $fileName = "solvia-nova-{$type}-report-" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'projects') {
                fputcsv($handle, ['Code', 'Name', 'Client', 'Status', 'Health', 'Revenue', 'Budget', 'Cost', 'Profit']);
                foreach (Project::with('client')->get() as $p) {
                    fputcsv($handle, [
                        $p->project_code,
                        $p->name,
                        $p->client ? $p->client->name : 'N/A',
                        $p->status,
                        $p->health,
                        $p->revenue,
                        $p->budget,
                        $p->actual_cost,
                        $p->profit,
                    ]);
                }
            } elseif ($type === 'finance') {
                fputcsv($handle, ['Type', 'Number', 'Date', 'Amount', 'Category', 'Notes']);
                foreach (Income::all() as $inc) {
                    fputcsv($handle, ['Income', $inc->income_number, $inc->date->toDateString(), $inc->amount, $inc->category, $inc->notes]);
                }
                foreach (Expense::all() as $exp) {
                    fputcsv($handle, ['Expense', $exp->expense_number, $exp->date->toDateString(), $exp->amount, $exp->category, $exp->notes]);
                }
            } elseif ($type === 'assets') {
                fputcsv($handle, ['Tag', 'Brand', 'Model', 'Serial', 'Lifecycle Status', 'Condition', 'Holder', 'Cost']);
                foreach (Asset::with('currentHolder')->get() as $a) {
                    fputcsv($handle, [
                        $a->asset_tag,
                        $a->brand,
                        $a->model,
                        $a->serial_number,
                        $a->lifecycle_status,
                        $a->condition,
                        $a->currentHolder ? $a->currentHolder->name : 'Unassigned',
                        $a->accumulated_cost,
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}
