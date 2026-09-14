<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function profile()
    {
        $this->authorizeCompany();

        $profile = CompanyProfile::firstOrCreate(['id' => 1], [
            'name' => 'Solvia.Nova',
            'legal_name' => 'PT Solvia Nova Teknologi',
            'email' => 'contact@solvia.nova',
            'phone' => '+62 21 8899 0011',
            'address' => 'Solvia Tower Lt. 12, Mega Kuningan, Jakarta Selatan',
            'website' => 'https://solvia.nova',
            'tax_number' => '01.234.567.8-012.000',
            'currency' => 'IDR',
        ]);

        return view('company.profile', compact('profile'));
    }

    public function updateProfile(Request $request)
    {
        $this->authorizeCompany();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'website' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'health_overdue_off_track' => 'nullable|integer|min:1|max:20',
            'health_progress_gap_off_track' => 'nullable|integer|min:1|max:100',
            'health_progress_gap_at_risk' => 'nullable|integer|min:1|max:100',
        ]);

        $data['settings'] = [
            'health' => [
                'overdue_tasks_off_track' => (int) ($request->input('health_overdue_off_track', 3)),
                'progress_gap_off_track' => (int) ($request->input('health_progress_gap_off_track', 30)),
                'progress_gap_at_risk' => (int) ($request->input('health_progress_gap_at_risk', 15)),
            ],
        ];
        unset($data['health_overdue_off_track'], $data['health_progress_gap_off_track'], $data['health_progress_gap_at_risk']);

        $profile = CompanyProfile::first();
        $old = $profile->toArray();
        $profile->update($data);

        AuditLogger::log('update', 'CompanyProfile', $profile->id, $old, $profile->toArray(), 'Company profile updated');
        return back()->with('success', 'Company profile updated successfully.');
    }

    public function users(Request $request)
    {
        $this->authorizeCompany();

        $query = User::with('team')->orderBy('role')->orderBy('name');
        $filters = [
            'search' => $request->get('search'),
            'role' => $request->get('role'),
            'status' => $request->get('status'),
        ];

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(12)->withQueryString();
        $teams = Team::all();
        $activeCount = User::where('status', 'active')->count();
        $adminCount = User::where('role', 'super_admin')->count();
        $devCount = User::whereIn('role', ['frontend_developer', 'backend_developer', 'iot_engineer'])->count();

        return view('company.users', compact('users', 'teams', 'filters', 'activeCount', 'adminCount', 'devCount'));
    }

    public function storeUser(Request $request)
    {
        $this->authorizeCompany();

        if (empty($request->team_id)) {
            $request->merge(['team_id' => null]);
        }
        if (empty($request->status)) {
            $request->merge(['status' => 'active']);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:super_admin,content_creator,designer,frontend_developer,backend_developer,iot_engineer,viewer',
            'department' => 'nullable|string|max:255',
            'team_id' => 'nullable|exists:teams,id',
            'phone' => 'nullable|string|max:50',
            'join_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        $data['status'] = $data['status'] ?? 'active';
        $data['join_date'] = $data['join_date'] ?? now()->toDateString();

        $user = User::create($data);

        AuditLogger::log('create', 'User', $user->id, null, $user->toArray(), "New user created: {$user->name} ({$user->role})");
        return back()->with('success', "User {$user->name} added successfully.");
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorizeCompany();

        if (empty($request->team_id)) {
            $request->merge(['team_id' => null]);
        }
        if (empty($request->status)) {
            $request->merge(['status' => 'active']);
        }
        if (empty($request->role)) {
            $request->merge(['role' => $user->role]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'nullable|in:super_admin,content_creator,designer,frontend_developer,backend_developer,iot_engineer,viewer',
            'department' => 'nullable|string|max:255',
            'team_id' => 'nullable|exists:teams,id',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive,suspended',
            'password' => 'nullable|min:6',
        ]);

        $data['role'] = $data['role'] ?? $user->role;
        $data['status'] = $data['status'] ?? 'active';

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $old = $user->toArray();
        $user->update($data);

        AuditLogger::log('update', 'User', $user->id, $old, $user->toArray(), "User {$user->name} updated");
        return back()->with('success', "User {$user->name} updated successfully.");
    }

    public function destroyUser(User $user)
    {
        $this->authorizeCompany();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $old = $user->toArray();
        $user->delete();

        AuditLogger::log('delete', 'User', $user->id, $old, null, "User {$name} deleted");
        return back()->with('success', "User {$name} deleted successfully.");
    }

    public function toggleStatus(User $user)
    {
        $this->authorizeCompany();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $old = $user->status;
        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        AuditLogger::log('permission_change', 'User', $user->id, ['status' => $old], ['status' => $user->status], "User {$user->name} status toggled to {$user->status}");
        return back()->with('success', "{$user->name} is now {$user->status}.");
    }

    public function teams()
    {
        $this->authorizeCompany();

        $teams = Team::with('lead', 'members')->get();
        $users = User::where('status', 'active')->get();
        return view('company.teams', compact('teams', 'users'));
    }

    public function storeTeam(Request $request)
    {
        $this->authorizeCompany();

        if (!$request->filled('code') && $request->filled('name')) {
            $codeCandidate = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($request->name, 0, 6)));
            $request->merge(['code' => $codeCandidate ?: 'TEAM-' . rand(100, 999)]);
        }
        if (!$request->filled('lead_id')) {
            $request->merge(['lead_id' => null]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:teams,code',
            'description' => 'nullable|string',
            'lead_id' => 'nullable|exists:users,id',
        ]);

        $team = Team::create($data);
        AuditLogger::log('create', 'Team', $team->id, null, $team->toArray(), "New team created: {$team->name}");
        return back()->with('success', "Team {$team->name} created successfully.");
    }

    public function addTeamMember(Request $request, Team $team)
    {
        $this->authorizeCompany();

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($data['user_id']);
        $user->update(['team_id' => $team->id]);

        Notification::create([
            'user_id' => $user->id,
            'type' => 'team',
            'title' => 'Bergabung ke Tim: ' . $team->name,
            'message' => Auth::user()->name . " menempatkan Anda ke dalam divisi tim '{$team->name}'.",
            'action_url' => route('dashboard'),
            'level' => 'info',
        ]);

        return back()->with('success', "{$user->name} added to team {$team->name}.");
    }

    public function removeTeamMember(Team $team, User $user)
    {
        $this->authorizeCompany();

        if ($user->team_id === $team->id) {
            $user->update(['team_id' => null]);
        }

        return back()->with('success', "{$user->name} removed from team {$team->name}.");
    }

    public function destroyTeam(Team $team)
    {
        $this->authorizeCompany();

        $name = $team->name;
        User::where('team_id', $team->id)->update(['team_id' => null]);
        $old = $team->toArray();
        $team->delete();

        AuditLogger::log('delete', 'Team', $team->id, $old, null, "Team {$name} deleted");
        return back()->with('success', "Team {$name} deleted successfully.");
    }

    public function clients(Request $request)
    {
        $this->authorizeCompany();

        $query = Client::withCount('projects')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $clients    = $query->paginate(15)->withQueryString();
        $activeCount    = Client::where('status', 'active')->count();
        $projectsCount  = Client::withCount('projects')->get()->sum('projects_count');
        $filters = ['search' => $request->get('search'), 'status' => $request->get('status')];

        return view('company.clients', compact('clients', 'activeCount', 'projectsCount', 'filters'));
    }

    public function storeClient(Request $request)
    {
        $this->authorizeCompany();

        if (!$request->filled('client_code') && $request->filled('name')) {
            $codeCandidate = 'CLT-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($request->name, 0, 4))) . '-' . rand(100, 999);
            $request->merge(['client_code' => $codeCandidate]);
        }

        $data = $request->validate([
            'client_code' => 'required|string|unique:clients,client_code',
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'website' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $client = Client::create($data);
        AuditLogger::log('create', 'Client', $client->id, null, $client->toArray(), "New client registered: {$client->name}");
        return back()->with('success', "Client {$client->name} registered successfully.");
    }

    public function updateClient(Request $request, Client $client)
    {
        $this->authorizeCompany();

        $data = $request->validate([
            'client_code' => 'required|string|unique:clients,client_code,' . $client->id,
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'website' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $old = $client->toArray();
        $client->update($data);
        AuditLogger::log('update', 'Client', $client->id, $old, $client->toArray(), "Client {$client->name} updated");
        return back()->with('success', "Client {$client->name} updated successfully.");
    }

    public function destroyClient(Client $client)
    {
        $this->authorizeCompany();

        $name = $client->name;
        $old = $client->toArray();
        $client->delete();
        AuditLogger::log('delete', 'Client', $client->id, $old, null, "Client {$name} deleted");
        return back()->with('success', "Client {$name} deleted successfully.");
    }

    private function authorizeCompany(): void
    {
        if (!Auth::user()?->isSuperAdmin() && !Auth::user()?->hasPermission('company.manage')) {
            abort(403, 'Company management is restricted to Super Admin.');
        }
    }
}
