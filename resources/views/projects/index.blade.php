@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ newProjectModal: {{ (isset($errors) && $errors->any()) ? 'true' : 'false' }} }">
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-white text-lg">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center justify-between">
            <span>{{ session('error') }}</span>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-400 hover:text-white text-lg">&times;</button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
            <div class="font-bold mb-1">Failed to create project:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- HEADER & FILTERS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Projects
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $projects->count() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Super Admin Project Orchestration & Operational Execution</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Pills -->
            <div class="flex items-center rounded-xl bg-slate-900 border border-slate-800 p-1 text-xs">
                <a href="{{ route('projects.index') }}" class="px-2.5 py-1 rounded-lg {{ !request('status') && !request('health') ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">All</a>
                <a href="{{ route('projects.index', ['status' => 'active']) }}" class="px-2.5 py-1 rounded-lg {{ request('status') === 'active' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">Active</a>
                <a href="{{ route('projects.index', ['health' => 'at_risk']) }}" class="px-2.5 py-1 rounded-lg {{ request('health') === 'at_risk' ? 'bg-amber-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">At Risk</a>
                <a href="{{ route('projects.index', ['health' => 'off_track']) }}" class="px-2.5 py-1 rounded-lg {{ request('health') === 'off_track' ? 'bg-rose-600 text-white font-bold' : 'text-slate-400 hover:text-white' }}">Off Track</a>
            </div>

            @if(Auth::user()->isSuperAdmin() || Auth::user()->role !== 'viewer')
            <button @click="newProjectModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Project
            </button>
            @endif
        </div>
    </div>

    <!-- PROJECT CARDS GRID (Desktop & Mobile Adaptive - Section BK) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($projects as $project)
            @php
                $avgProgress = (int) round($project->tasks->avg('progress') ?? 0);
                $healthStyle = match($project->health) {
                    'off_track' => 'border-rose-500/40 bg-rose-500/10 text-rose-300',
                    'at_risk' => 'border-amber-500/40 bg-amber-500/10 text-amber-300',
                    default => 'border-emerald-500/40 bg-emerald-500/10 text-emerald-300',
                };
            @endphp
            <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-indigo-500/40 transition flex flex-col justify-between group shadow-lg">
                <div>
                    <!-- Top Tag & Health -->
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-xs font-mono font-bold text-slate-400">{{ $project->project_code }}</span>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full border {{ $healthStyle }}">
                                {{ str_replace('_', ' ', $project->health) }}
                            </span>
                            <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-slate-800 text-slate-300">
                                {{ $project->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Project Title & Client -->
                    <a href="{{ route('projects.show', $project->id) }}" class="block">
                        <h2 class="font-bold text-base text-white group-hover:text-indigo-400 transition">{{ $project->name }}</h2>
                    </a>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Client: <span class="text-slate-200 font-medium">{{ $project->client ? $project->client->name : 'Internal' }}</span>
                    </p>
                    <p class="text-xs text-slate-500 line-clamp-2 mt-2">{{ $project->description ?? 'No project description provided.' }}</p>

                    <!-- Progress Bar -->
                    <div class="mt-4 space-y-1.5">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-400">Progress</span>
                            <span class="font-mono font-bold text-white">{{ $avgProgress }}%</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full {{ $project->health === 'off_track' ? 'bg-rose-500' : ($project->health === 'at_risk' ? 'bg-amber-500' : 'bg-indigo-500') }} transition-all"
                                 style="width: {{ $avgProgress }}%"></div>
                        </div>
                    </div>

                    <!-- Financial / Progress & Timeline Summary -->
                    <div class="mt-4 pt-3 border-t border-slate-800/80 grid grid-cols-2 gap-2 text-xs">
                        @if(Auth::user()->isSuperAdmin())
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase">Revenue</span>
                            <span class="font-mono font-bold text-slate-200">Rp {{ number_format($project->revenue, 0, ',', '.') }}</span>
                        </div>
                        @else
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase">Tasks Overview</span>
                            <span class="font-mono font-bold text-slate-200">{{ $project->tasks->where('status', 'done')->count() }} / {{ $project->tasks->count() }} Done</span>
                        </div>
                        @endif
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase">Deadline</span>
                            <span class="font-mono font-bold text-slate-200">{{ $project->deadline->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer & Action -->
                <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs">
                    <!-- Team Avatars -->
                    <div class="flex -space-x-1.5 overflow-hidden">
                        @foreach($project->members->take(4) as $m)
                            <div class="inline-block h-6 w-6 rounded-full ring-2 ring-slate-900 bg-indigo-600 flex items-center justify-center font-bold text-[9px] text-white" title="{{ $m->user->name }} ({{ $m->role }})">
                                {{ strtoupper(substr($m->user->name, 0, 1)) }}
                            </div>
                        @endforeach
                        @if($project->members->count() > 4)
                            <div class="inline-block h-6 w-6 rounded-full ring-2 ring-slate-900 bg-slate-800 flex items-center justify-center font-bold text-[9px] text-slate-300">
                                +{{ $project->members->count() - 4 }}
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('projects.show', $project->id) }}" class="px-3 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold transition flex items-center gap-1">
                        View Workspace
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-400 text-xs">
                No projects found matching the criteria.
            </div>
        @endforelse
    </div>

    <!-- MODAL: NEW PROJECT -->
    @if(Auth::user()->isSuperAdmin() || Auth::user()->role !== 'viewer')
    <div x-cloak x-show="newProjectModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="newProjectModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="newProjectModal = false" aria-hidden="true"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="newProjectModal = false">
            <div x-show="newProjectModal" x-transition @click.stop class="relative z-20 w-full max-w-xl rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Create New Project</h3>
                    <button @click="newProjectModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                @if($clients->isEmpty())
                    <div class="mb-4 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs">
                        ⚠️ No clients registered yet. Projects require a client. <a href="{{ route('company.clients') }}" class="underline font-bold text-white hover:text-indigo-300">Click here to register a client first</a>.
                    </div>
                @endif

                <form action="{{ route('projects.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Project Code *</label>
                            <input type="text" name="project_code" value="PRJ-{{ strtoupper(uniqid()) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Project Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Smart Telemetry Dashboard" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Client *</label>
                            <select name="client_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                @forelse($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->client_code }})</option>
                                @empty
                                    <option value="" disabled selected>No clients available</option>
                                @endforelse
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Project Type *</label>
                            <select name="project_type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="Web Platform">Web Platform</option>
                                <option value="Mobile Application">Mobile Application</option>
                                <option value="IoT Engineering">IoT Engineering</option>
                                <option value="Brand Identity & Content">Brand Identity & Content</option>
                                <option value="Infrastructure Modernization">Infrastructure Modernization</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Start Date *</label>
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Deadline *</label>
                            <input type="date" name="deadline" value="{{ date('Y-m-d', strtotime('+45 days')) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    @if(Auth::user()->isSuperAdmin())
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Revenue (Contract Value) *</label>
                            <input type="number" name="revenue" value="75000000" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Budget Allocation *</label>
                            <input type="number" name="budget" value="45000000" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Initial Status *</label>
                            <select name="status" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="planning">Planning</option>
                                <option value="active" selected>Active</option>
                                <option value="on_hold">On Hold</option>
                            </select>
                        </div>
                    </div>
                    @else
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Initial Status *</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            <option value="planning">Planning</option>
                            <option value="active" selected>Active</option>
                            <option value="on_hold">On Hold</option>
                        </select>
                    </div>
                    @endif

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Brief objectives and technical scope" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="newProjectModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-lg shadow-indigo-600/30">Create Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
