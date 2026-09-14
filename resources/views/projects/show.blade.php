@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $activeTab }}',
    taskModal: false,
    milestoneModal: false,
    memberModal: false
}">

    <!-- PROJECT WORKSPACE HEADER (Section BM) -->
    <div class="p-5 sm:p-6 rounded-3xl bg-slate-900/90 border border-slate-800/80 shadow-xl relative overflow-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-mono font-bold text-slate-400">{{ $project->project_code }}</span>
                    <h1 class="text-xl sm:text-2xl font-black text-white">{{ $project->name }}</h1>
                    @php
                        $healthBadge = match($project->health) {
                            'off_track' => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
                            'at_risk' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                            default => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full border {{ $healthBadge }}">
                        {{ str_replace('_', ' ', $project->health) }}
                    </span>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded bg-slate-800 text-slate-300">
                        {{ $project->status }}
                    </span>
                </div>
                <p class="text-xs text-slate-400">
                    Client: <span class="text-slate-200 font-semibold">{{ $project->client ? $project->client->name : 'Internal' }}</span> •
                    Timeline: <span class="text-slate-300 font-mono">{{ $project->start_date->format('d M Y') }} &rarr; {{ $project->deadline->format('d M Y') }}</span> •
                    Type: <span class="text-indigo-400 font-medium">{{ $project->project_type }}</span>
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                @if(Auth::user()->isSuperAdmin() || $project->members->contains('user_id', Auth::id()))
                <button @click="taskModal = true" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Task
                </button>
                <button @click="milestoneModal = true" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition">
                    + Milestone
                </button>
                @endif
                @if(Auth::user()->isSuperAdmin())
                <button @click="memberModal = true" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition">
                    + Team Member
                </button>
                @endif
            </div>
        </div>

        <!-- PROGRESS BAR -->
        @php $calcProgress = (int) round($project->tasks->avg('progress') ?? 0); @endphp
        <div class="mt-5 pt-4 border-t border-slate-800/80 space-y-1.5">
            <div class="flex justify-between text-xs">
                <span class="text-slate-400 font-medium">Overall Progress</span>
                <span class="font-mono font-bold text-white">{{ $calcProgress }}%</span>
            </div>
            <div class="w-full h-2.5 rounded-full bg-slate-800 overflow-hidden">
                <div class="h-full rounded-full {{ $project->health === 'off_track' ? 'bg-rose-500' : ($project->health === 'at_risk' ? 'bg-amber-500' : 'bg-emerald-500') }} transition-all"
                     style="width: {{ $calcProgress }}%"></div>
            </div>
        </div>
    </div>

    <!-- WORKSPACE TABS (Adaptive Horizontal Scrollable - Section BM) -->
    <div class="border-b border-slate-800 flex items-center gap-1 overflow-x-auto touch-scroll -mx-3.5 px-3.5 sm:mx-0 sm:px-0 text-xs font-semibold pb-1">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-indigo-500 text-indigo-400 bg-slate-900/60' : 'border-transparent text-slate-400 hover:text-slate-200'" class="px-3.5 py-2 rounded-xl border-b-2 transition whitespace-nowrap">
            Overview
        </button>
        <button @click="activeTab = 'tasks'" :class="activeTab === 'tasks' ? 'border-indigo-500 text-indigo-400 bg-slate-900/60' : 'border-transparent text-slate-400 hover:text-slate-200'" class="px-3.5 py-2 rounded-xl border-b-2 transition whitespace-nowrap flex items-center gap-1.5">
            Tasks & Milestones
            <span class="px-1.5 py-0.2 bg-slate-800 text-[10px] rounded-full text-slate-300 font-mono">{{ $project->tasks->count() }}</span>
        </button>
        <button @click="activeTab = 'team'" :class="activeTab === 'team' ? 'border-indigo-500 text-indigo-400 bg-slate-900/60' : 'border-transparent text-slate-400 hover:text-slate-200'" class="px-3.5 py-2 rounded-xl border-b-2 transition whitespace-nowrap flex items-center gap-1.5">
            Team
            <span class="px-1.5 py-0.2 bg-slate-800 text-[10px] rounded-full text-slate-300 font-mono">{{ $project->members->count() }}</span>
        </button>
        <button @click="activeTab = 'progress'" :class="activeTab === 'progress' ? 'border-indigo-500 text-indigo-400 bg-slate-900/60' : 'border-transparent text-slate-400 hover:text-slate-200'" class="px-3.5 py-2 rounded-xl border-b-2 transition whitespace-nowrap">
            Daily Progress History
        </button>
        <button @click="activeTab = 'blockers'" :class="activeTab === 'blockers' ? 'border-indigo-500 text-indigo-400 bg-slate-900/60' : 'border-transparent text-slate-400 hover:text-slate-200'" class="px-3.5 py-2 rounded-xl border-b-2 transition whitespace-nowrap flex items-center gap-1.5">
            Blockers
            @if($project->blockers->whereIn('status', ['open', 'in_progress'])->count() > 0)
                <span class="px-1.5 py-0.2 bg-rose-500/20 text-rose-300 text-[10px] rounded-full font-mono font-bold">{{ $project->blockers->whereIn('status', ['open', 'in_progress'])->count() }}</span>
            @endif
        </button>
        @if(Auth::user()->isSuperAdmin())
        <button @click="activeTab = 'finance'" :class="activeTab === 'finance' ? 'border-indigo-500 text-indigo-400 bg-slate-900/60' : 'border-transparent text-slate-400 hover:text-slate-200'" class="px-3.5 py-2 rounded-xl border-b-2 transition whitespace-nowrap">
            Project Finance
        </button>
        @endif
        <button @click="activeTab = 'resources'" :class="activeTab === 'resources' ? 'border-indigo-500 text-indigo-400 bg-slate-900/60' : 'border-transparent text-slate-400 hover:text-slate-200'" class="px-3.5 py-2 rounded-xl border-b-2 transition whitespace-nowrap">
            Resources
        </button>
        @if(Auth::user()->isSuperAdmin())
        <button @click="activeTab = 'closure'" :class="activeTab === 'closure' ? 'border-indigo-500 text-indigo-400 bg-slate-900/60' : 'border-transparent text-slate-400 hover:text-slate-200'" class="px-3.5 py-2 rounded-xl border-b-2 transition whitespace-nowrap flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Closure Review
        </button>
        @endif
    </div>

    <!-- TAB 1: OVERVIEW -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        @if(Auth::user()->isSuperAdmin())
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Contract Revenue</span>
                <p class="text-lg font-black text-white mt-1 font-mono">Rp {{ number_format($project->revenue, 0, ',', '.') }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Actual Cost</span>
                <p class="text-lg font-black text-rose-400 mt-1 font-mono">Rp {{ number_format($project->actual_cost, 0, ',', '.') }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Estimated Profit</span>
                <p class="text-lg font-black {{ $project->profit >= 0 ? 'text-emerald-400' : 'text-rose-400' }} mt-1 font-mono">
                    Rp {{ number_format($project->profit, 0, ',', '.') }}
                </p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Profit Margin</span>
                <p class="text-lg font-black text-indigo-400 mt-1 font-mono">{{ $project->profit_margin }}%</p>
            </div>
        </div>
        @else
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Total Tasks</span>
                <p class="text-lg font-black text-white mt-1 font-mono">{{ $project->tasks->count() }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Completed Tasks</span>
                <p class="text-lg font-black text-emerald-400 mt-1 font-mono">{{ $project->tasks->where('status', 'done')->count() }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Active Milestones</span>
                <p class="text-lg font-black text-indigo-400 mt-1 font-mono">{{ $project->milestones->where('status', 'in_progress')->count() }} / {{ $project->milestones->count() }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                <span class="text-xs text-slate-400 font-medium">Open Blockers</span>
                <p class="text-lg font-black {{ $project->blockers->whereIn('status', ['open', 'in_progress'])->count() > 0 ? 'text-rose-400' : 'text-slate-200' }} mt-1 font-mono">
                    {{ $project->blockers->whereIn('status', ['open', 'in_progress'])->count() }}
                </p>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Project Scope & Description</h3>
                <p class="text-xs text-slate-300 leading-relaxed">{{ $project->description ?? 'No detailed description provided.' }}</p>

                <h3 class="text-xs font-bold text-white uppercase tracking-wider pt-3">Milestone Progress</h3>
                <div class="space-y-2">
                    @forelse($project->milestones as $m)
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-white">{{ $m->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $m->start_date->format('d M') }} - {{ $m->deadline->format('d M Y') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono font-bold text-slate-200">{{ $m->progress }}%</span>
                                <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-slate-800 text-slate-300">{{ $m->status }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">No milestones registered yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- Health Diagnostic Reasons -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Project Health Engine</h3>
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Current Health:</span>
                        <span class="font-bold uppercase {{ $project->health === 'off_track' ? 'text-rose-400' : ($project->health === 'at_risk' ? 'text-amber-400' : 'text-emerald-400') }}">
                            {{ str_replace('_', ' ', $project->health) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Active Tasks:</span>
                        <span class="font-bold text-white">{{ $project->tasks->where('status', '!=', 'done')->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Active Blockers:</span>
                        <span class="font-bold text-rose-400">{{ $project->blockers->whereIn('status', ['open', 'in_progress'])->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: TASKS & KANBAN (Section J & K) -->
    <div x-show="activeTab === 'tasks'" class="space-y-6">
        <!-- Kanban Columns -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
            @php
                $columns = [
                    'to_do' => ['title' => 'To Do', 'color' => 'slate'],
                    'in_progress' => ['title' => 'In Progress', 'color' => 'indigo'],
                    'waiting_review' => ['title' => 'Review', 'color' => 'purple'],
                    'blocked' => ['title' => 'Blocked', 'color' => 'rose'],
                    'done' => ['title' => 'Done', 'color' => 'emerald'],
                ];
            @endphp

            @foreach($columns as $colKey => $col)
                <div class="rounded-2xl bg-slate-900/80 border border-slate-800 p-3 flex flex-col max-h-[680px]">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800">
                        <span class="font-bold text-xs text-white uppercase tracking-wider">{{ $col['title'] }}</span>
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
                            {{ $project->tasks->where('status', $colKey)->count() }}
                        </span>
                    </div>

                    <div class="space-y-2 overflow-y-auto flex-1 pr-1">
                        @forelse($project->tasks->where('status', $colKey) as $t)
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 transition text-xs space-y-2 shadow">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.2 rounded {{ $t->priority === 'urgent' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-800 text-slate-400' }}">
                                        {{ $t->priority }}
                                    </span>
                                    @if($t->deadline)
                                        <span class="text-[10px] font-mono text-slate-400">{{ $t->deadline->format('d M') }}</span>
                                    @endif
                                </div>

                                <p class="font-bold text-white text-xs leading-snug">{{ $t->title }}</p>

                                @if($t->blockedBy->count() > 0)
                                    <div class="p-1.5 rounded bg-rose-950/40 border border-rose-900/50 text-[10px] text-rose-300 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Blocked by {{ $t->blockedBy->count() }} task(s)</span>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between pt-2 border-t border-slate-800 text-[11px] text-slate-400">
                                    <span class="truncate">{{ $t->assignee ? $t->assignee->name : 'Unassigned' }}</span>
                                    <span class="font-mono font-bold text-slate-300">{{ $t->progress }}%</span>
                                </div>

                                @if(!$t->assignee_id)
                                    <form action="{{ route('tasks.claim', $t->id) }}" method="POST" class="pt-1">
                                        @csrf
                                        <button type="submit" class="w-full py-1 px-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[11px] flex items-center justify-center gap-1 shadow transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Klaim Tugas
                                        </button>
                                    </form>
                                @elseif($t->assignee_id === Auth::id() && in_array($t->status, ['to_do', 'backlog']))
                                    <form action="{{ route('tasks.claim', $t->id) }}" method="POST" class="pt-1">
                                        @csrf
                                        <button type="submit" class="w-full py-1 px-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] flex items-center justify-center gap-1 shadow transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                            Mulai Kerjakan (Klaim)
                                        </button>
                                    </form>
                                @endif

                                <!-- Action Status Dropdown -->
                                <form action="{{ route('tasks.status.update', $t->id) }}" method="POST" class="pt-1">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="w-full text-[10px] rounded bg-slate-900 border border-slate-800 text-slate-300 py-1 px-1.5 focus:outline-none">
                                        <option value="to_do" {{ $t->status === 'to_do' ? 'selected' : '' }}>Move: To Do</option>
                                        <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>Move: In Progress</option>
                                        <option value="waiting_review" {{ $t->status === 'waiting_review' ? 'selected' : '' }}>Move: Review</option>
                                        <option value="blocked" {{ $t->status === 'blocked' ? 'selected' : '' }}>Move: Blocked</option>
                                        <option value="done" {{ $t->status === 'done' ? 'selected' : '' }}>Move: Done</option>
                                    </select>
                                </form>
                            </div>
                        @empty
                            <p class="text-center text-[11px] text-slate-600 py-4">No tasks</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 3: TEAM MEMBERS (Section H: Dynamic composition) -->
    <div x-show="activeTab === 'team'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Project Team Allocation</h3>
            @if(Auth::user()->isSuperAdmin())
            <button @click="memberModal = true" class="px-3 py-1.5 rounded-xl bg-indigo-600 text-xs font-semibold text-white">
                Assign User
            </button>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($project->members as $member)
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-white">
                            {{ strtoupper(substr($member->user->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="font-bold text-white text-sm">{{ $member->user->name }}</p>
                            <p class="text-indigo-400 font-medium">{{ $member->role }}</p>
                            <p class="text-slate-500 text-[10px] mt-0.5">{{ $member->responsibility ?? 'Active Contributor' }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-slate-800 text-slate-300">
                        {{ $member->status }}
                    </span>
                </div>
            @empty
                <p class="col-span-full text-center text-xs text-slate-500 py-6">No team members assigned yet.</p>
            @endforelse
        </div>
    </div>

    <!-- TAB 4: DAILY PROGRESS TIMELINE (Section L) -->
    <div x-show="activeTab === 'progress'" class="space-y-4">
        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Historical Progress Timeline</h3>
        <div class="space-y-3">
            @forelse($project->dailyProgress->sortByDesc('date') as $dp)
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-white">{{ $dp->user->name }}</span>
                            <span class="text-slate-400 font-mono text-[11px]">({{ $dp->date->format('d M Y') }})</span>
                        </div>
                        <span class="font-mono font-bold text-indigo-400">{{ $dp->progress }}% completed • {{ $dp->working_hours }}h</span>
                    </div>
                    <p class="text-slate-300"><strong class="text-slate-400">Completed Work:</strong> {{ $dp->completed_work }}</p>
                    <p class="text-slate-300"><strong class="text-slate-400">Next Plan:</strong> {{ $dp->next_plan }}</p>
                    @if($dp->blocker)
                        <p class="text-rose-400 font-semibold"><strong class="text-rose-300">Blocker:</strong> {{ $dp->blocker }}</p>
                    @endif
                </div>
            @empty
                <p class="text-center text-xs text-slate-500 py-6">No progress logged for this project yet.</p>
            @endforelse
        </div>
    </div>

    <!-- TAB 5: BLOCKERS (Section M) -->
    <div x-show="activeTab === 'blockers'" class="space-y-4">
        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Reported Operational Blockers</h3>
        <div class="space-y-3">
            @forelse($project->blockers as $b)
                <div class="p-4 rounded-2xl bg-slate-900 border {{ $b->status === 'resolved' ? 'border-slate-800' : 'border-rose-500/40 bg-rose-950/10' }} text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-sm text-white">{{ $b->title }}</h4>
                            <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-rose-500/20 text-rose-300">{{ $b->priority }}</span>
                            <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-slate-800 text-slate-300">{{ $b->status }}</span>
                        </div>
                        <span class="text-slate-400 text-[10px]">Reported by {{ $b->reporter ? $b->reporter->name : 'N/A' }}</span>
                    </div>
                    <p class="text-slate-300">{{ $b->description }}</p>

                    @if($b->status !== 'resolved' && Auth::user()->isSuperAdmin())
                        <form action="{{ route('blockers.resolve', $b->id) }}" method="POST" class="pt-2 flex items-center gap-2">
                            @csrf
                            <input type="text" name="resolution_notes" placeholder="Resolution notes / action taken..." required class="flex-1 px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs">
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs">Mark Resolved</button>
                        </form>
                    @elseif($b->status === 'resolved')
                        <p class="text-emerald-400 text-[11px] pt-1"><strong>Resolved:</strong> {{ $b->resolution_notes }} ({{ $b->resolved_date ? $b->resolved_date->format('d M Y') : '' }})</p>
                    @endif
                </div>
            @empty
                <p class="text-center text-xs text-slate-500 py-6">No blockers reported. Everything running smoothly!</p>
            @endforelse
        </div>
    </div>

    <!-- TAB 6: PROJECT FINANCE (Section P) -->
    @if(Auth::user()->isSuperAdmin())
    <div x-show="activeTab === 'finance'" class="space-y-4">
        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Financial Breakdown</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Expenses -->
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-2">
                <h4 class="font-bold text-xs text-white uppercase">Project Expenses</h4>
                <div class="space-y-1.5 max-h-60 overflow-y-auto">
                    @forelse($project->expenses as $exp)
                        <div class="p-2 rounded-xl bg-slate-950 flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-white">{{ $exp->category }}</p>
                                <p class="text-[10px] text-slate-400">{{ $exp->notes }}</p>
                            </div>
                            <span class="font-mono font-bold text-rose-400">Rp {{ number_format($exp->amount, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 py-3">No expenses recorded against this project.</p>
                    @endforelse
                </div>
            </div>

            <!-- Invoices -->
            <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-2">
                <h4 class="font-bold text-xs text-white uppercase">Client Invoices</h4>
                <div class="space-y-1.5 max-h-60 overflow-y-auto">
                    @forelse($project->invoices as $inv)
                        <div class="p-2 rounded-xl bg-slate-950 flex items-center justify-between text-xs">
                            <div>
                                <p class="font-bold text-white">{{ $inv->invoice_number }}</p>
                                <p class="text-[10px] text-slate-400">Due: {{ $inv->due_date->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-mono font-bold text-white">Rp {{ number_format($inv->total, 0, ',', '.') }}</p>
                                <span class="text-[9px] uppercase font-bold text-indigo-400">{{ $inv->payment_status }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 py-3">No invoices generated for this project yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 7: RESOURCES (Section BE) -->
    <div x-show="activeTab === 'resources'" class="space-y-4">
        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Allocated Company Resources</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($project->resources as $res)
                <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 text-xs">
                    <span class="text-[9px] uppercase font-bold text-indigo-400">{{ $res->category }}</span>
                    <p class="font-bold text-white mt-1">{{ $res->name }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">{{ $res->resource_code }}</p>
                </div>
            @empty
                <p class="col-span-full text-center text-xs text-slate-500 py-6">No dedicated resources linked directly to this project.</p>
            @endforelse
        </div>
    </div>

    <!-- TAB 8: CLOSURE REVIEW (Section BF) -->
    @if(Auth::user()->isSuperAdmin())
    <div x-show="activeTab === 'closure'" class="space-y-6">
        <div class="p-5 rounded-3xl bg-slate-900 border border-slate-800 space-y-4">
            <div>
                <h3 class="font-black text-base text-white">Project Closure Verification Checklist</h3>
                <p class="text-xs text-slate-400 mt-0.5">Projects must undergo full inspection before being officially archived as Completed.</p>
            </div>

            <div class="space-y-2.5">
                @foreach($closureEvaluation['checklist'] as $chk)
                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center {{ $chk['passed'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                @if($chk['passed']) &check; @else &times; @endif
                            </span>
                            <div>
                                <p class="font-bold text-white">{{ $chk['title'] }}</p>
                                <p class="text-[11px] text-slate-400">{{ $chk['detail'] }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded {{ $chk['passed'] ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                            {{ $chk['passed'] ? 'Verified' : 'Action Needed' }}
                        </span>
                    </div>
                @endforeach
            </div>

            @if($project->status !== 'completed')
                <form action="{{ route('projects.close', $project->id) }}" method="POST" class="pt-4 border-t border-slate-800 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Final Closure Summary & Handover Notes</label>
                        <textarea name="closure_notes" rows="2" placeholder="e.g. All deliverables approved by client, credentials archived, repository tagged." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <button type="submit" {{ !$closureEvaluation['can_close'] ? 'disabled' : '' }} class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-xs shadow-lg shadow-emerald-600/20 transition disabled:opacity-40 disabled:cursor-not-allowed">
                        Finalize & Close Project
                    </button>
                    @if(!$closureEvaluation['can_close'])
                        <span class="text-xs text-rose-400 block">Cannot close: You must resolve all tasks and open blockers first.</span>
                    @endif
                </form>
            @else
                <div class="p-3 rounded-xl bg-emerald-950/40 border border-emerald-500/30 text-xs text-emerald-300">
                    This project was officially completed and closed on {{ $project->closed_at ? $project->closed_at->format('d M Y H:i') : '' }}. Handover notes: {{ $project->closure_notes }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- MODAL: ADD TASK -->
    <div x-cloak x-show="taskModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="taskModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="taskModal = false" aria-hidden="true"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="taskModal = false">
            <div x-show="taskModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Create Task in Workspace</h3>
                    <button @click="taskModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form action="{{ route('projects.tasks.store', $project->id) }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Task Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Build authentication endpoints" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Assignee</label>
                            <select name="assignee_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="">Unassigned</option>
                                @foreach($allUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ str_replace('_', ' ', $u->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Milestone</label>
                            <select name="milestone_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="">None</option>
                                @foreach($project->milestones as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Priority *</label>
                            <select name="priority" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Initial Status *</label>
                            <select name="status" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="to_do" selected>To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="waiting_review">Waiting Review</option>
                                <option value="blocked">Blocked</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Deadline</label>
                            <input type="date" name="deadline" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Estimated Hours *</label>
                            <input type="number" step="0.5" name="estimated_hours" value="12" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Dependency: Blocked By Task (Section K)</label>
                        <select name="blocked_by_task_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            <option value="">No dependency (Can start immediately)</option>
                            @foreach($project->tasks as $otherTask)
                                <option value="{{ $otherTask->id }}">{{ $otherTask->title }} ({{ $otherTask->status }})</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="progress" value="0">

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="taskModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold">Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD MILESTONE -->
    <div x-cloak x-show="milestoneModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="milestoneModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="milestoneModal = false" aria-hidden="true"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="milestoneModal = false">
            <div x-show="milestoneModal" x-transition @click.stop class="relative z-20 w-full max-w-md rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Add Milestone</h3>
                    <button @click="milestoneModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form action="{{ route('projects.milestones.store', $project->id) }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Milestone Name *</label>
                        <input type="text" name="name" required placeholder="e.g. MVP Launch" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Start Date *</label>
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Deadline *</label>
                            <input type="date" name="deadline" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Status *</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                            <option value="pending">Pending</option>
                            <option value="in_progress" selected>In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="milestoneModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 font-bold text-white">Save Milestone</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: ADD TEAM MEMBER -->
    <div x-cloak x-show="memberModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="memberModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="memberModal = false" aria-hidden="true"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="memberModal = false">
            <div x-show="memberModal" x-transition @click.stop class="relative z-20 w-full max-w-md rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Assign Team Member</h3>
                    <button @click="memberModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form action="{{ route('projects.members.store', $project->id) }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">User *</label>
                        <select name="user_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                            @foreach($allUsers as $usr)
                                <option value="{{ $usr->id }}">{{ $usr->name }} ({{ str_replace('_', ' ', $usr->role) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Project Role * (Dynamic)</label>
                        <input type="text" name="role" required placeholder="e.g. Lead IoT Engineer, UI/UX Designer, Senior Backend" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Specific Responsibility</label>
                        <textarea name="responsibility" rows="2" placeholder="e.g. Firmware development and MQTT protocol integration" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="memberModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 font-bold text-white">Assign Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
