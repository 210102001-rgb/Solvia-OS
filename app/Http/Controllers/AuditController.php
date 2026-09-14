<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->hasPermission('audit.view')) {
            abort(403, 'Unauthorized.');
        }

        $query = AuditLog::with('actor');

        if ($request->filled('user_id')) {
            $query->where('actor_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs    = $query->orderByDesc('created_at')->paginate(25);
        $users   = \App\Models\User::orderBy('name')->get();
        $filters = $request->only(['user_id', 'action', 'entity_type', 'date']);

        return view('audit.index', compact('logs', 'users', 'filters'));
    }
}
