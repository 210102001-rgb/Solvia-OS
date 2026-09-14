<?php

namespace App\Http\Controllers;

use App\Models\AutomationLog;
use App\Models\AutomationRule;
use App\Services\AutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutomationController extends Controller
{
    public function __construct(protected AutomationService $automationService) {}

    public function index()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $rules = AutomationRule::withCount('logs')->get();
        $logs = AutomationLog::with('rule')->orderByDesc('created_at')->paginate(20);

        return view('automation.index', compact('rules', 'logs'));
    }

    public function toggleRule(AutomationRule $rule)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $rule->update(['is_active' => !$rule->is_active]);
        return back()->with('success', "Rule '{$rule->name}' " . ($rule->is_active ? 'activated' : 'deactivated') . ".");
    }

    public function runTrigger()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $results = $this->automationService->runAutomations();
        return back()->with('success', "Automation cycle executed. Processed " . count($results) . " active rules.");
    }
}
