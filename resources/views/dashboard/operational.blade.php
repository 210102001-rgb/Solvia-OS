@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ progressModal: false, blockerModal: false, selectedTask: '' }">

    <!-- WELCOME GREETING & QUICK ACTION BAR -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Executor Hub</h1>
                <span class="text-xs bg-emerald-500/20 text-emerald-300 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider capitalize">
                    {{ str_replace('_', ' ', $user->role) }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Hello, {{ $user->name }}! Here is your operational focus for today.</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="blockerModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-rose-500/30 hover:border-rose-400 text-xs font-semibold text-rose-300 transition">
                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Report Blocker
            </button>
            <button @click="progressModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Submit Daily Progress
            </button>
        </div>
    </div>

    <!-- DAILY PROGRESS REMINDER ALERT (If not submitted today) -->
    @if(!$hasSubmittedToday)
    <div class="p-4 rounded-2xl bg-amber-950/40 border border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="p-2 rounded-xl bg-amber-500/20 text-amber-400 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div>
                <p class="text-xs font-bold text-white">Daily Progress Not Yet Submitted</p>
                <p class="text-[11px] text-amber-300/80">Please submit your completed work and next plans before the end of the shift.</p>
            </div>
        </div>
        <button @click="progressModal = true" class="w-full sm:w-auto text-center px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-md transition">
            Log Progress Now
        </button>
    </div>
    @else
    <div class="p-3 rounded-xl bg-emerald-950/40 border border-emerald-500/30 flex items-center gap-2 text-xs text-emerald-300">
        <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>Great job! You have already submitted your daily progress for today.</span>
    </div>
    @endif

    <!-- QUICK STATS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800">
            <span class="text-xs font-semibold text-slate-400">My Active Tasks</span>
            <p class="text-xl font-black text-white mt-1">{{ $myTasks->count() }}</p>
            <p class="text-[10px] text-slate-500">Assigned to you</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800">
            <span class="text-xs font-semibold text-slate-400">Due Today</span>
            <p class="text-xl font-black {{ $todayTasks->count() > 0 ? 'text-amber-400' : 'text-slate-200' }} mt-1">{{ $todayTasks->count() }}</p>
            <p class="text-[10px] text-slate-500">Requires focus</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800">
            <span class="text-xs font-semibold text-slate-400">Reported Blockers</span>
            <p class="text-xl font-black {{ $myBlockers->count() > 0 ? 'text-rose-400' : 'text-emerald-400' }} mt-1">{{ $myBlockers->count() }}</p>
            <p class="text-[10px] text-slate-500">Awaiting resolution</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900/90 border border-slate-800">
            <span class="text-xs font-semibold text-slate-400">Assigned Assets</span>
            <p class="text-xl font-black text-white mt-1">{{ $myAssets->count() }}</p>
            <p class="text-[10px] text-slate-500">Hardware & devices</p>
        </div>
    </div>

    <!-- MAIN TWO COLUMN VIEW -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT 2 COLS: My Tasks (Adaptive Cards) & Progress History -->
        <div class="lg:col-span-2 space-y-6">

            <!-- MY TASKS LIST -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Tugas Saya (My Assigned Tasks)
                    </h2>
                    <span class="text-[11px] text-slate-400">{{ $myTasks->count() }} tugas aktif</span>
                </div>

                <div class="space-y-3">
                    @forelse($myTasks as $task)
                        @php
                            $prioBadge = match($task->priority) {
                                'urgent' => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
                                'high' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                                default => 'bg-slate-800 text-slate-300 border-slate-700',
                            };
                            $statusBadge = match($task->status) {
                                'blocked' => 'bg-rose-500/20 text-rose-400 border-rose-500/40',
                                'in_progress' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/40',
                                'waiting_review' => 'bg-purple-500/20 text-purple-300 border-purple-500/40',
                                default => 'bg-slate-800 text-slate-300 border-slate-700',
                            };
                        @endphp
                        <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800 hover:border-slate-700 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        @if($task->project)
                                            <a href="{{ route('projects.show', $task->project_id) }}?tab=tasks" class="font-bold text-sm text-white hover:text-indigo-400 transition truncate flex items-center gap-1.5" title="Buka Workspace Proyek">
                                                {{ $task->title }}
                                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        @else
                                            <h3 class="font-bold text-sm text-white truncate">{{ $task->title }}</h3>
                                        @endif
                                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded border {{ $prioBadge }}">
                                            {{ $task->priority }}
                                        </span>
                                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded border {{ $statusBadge }}">
                                            {{ str_replace('_', ' ', $task->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1">
                                        Proyek: <span class="text-slate-200 font-medium">{{ $task->project ? $task->project->name : 'General' }}</span>
                                        @if($task->milestone) • Milestone: <span class="text-slate-300">{{ $task->milestone->name }}</span> @endif
                                        @if($task->deadline) • Deadline: <span class="text-slate-200 font-mono">{{ $task->deadline->format('d M Y') }}</span> @endif
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    @if(in_array($task->status, ['to_do', 'backlog']))
                                        <form action="{{ route('tasks.claim', $task->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] shadow transition" title="Klaim dan Mulai Kerjakan">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                                Mulai
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Quick Status Toggle -->
                                    <form action="{{ route('tasks.status.update', $task->id) }}" method="POST">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="text-[11px] rounded-lg bg-slate-900 border border-slate-800 text-slate-200 py-1 px-2 focus:outline-none focus:border-indigo-500">
                                            <option value="to_do" {{ $task->status === 'to_do' ? 'selected' : '' }}>To Do</option>
                                            <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="waiting_review" {{ $task->status === 'waiting_review' ? 'selected' : '' }}>Waiting Review</option>
                                            <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>Mark Done</option>
                                        </select>
                                    </form>

                                    <button @click="progressModal = true; selectedTask = '{{ $task->id }}'" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition" title="Log Progress for Task">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-3 space-y-1">
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-slate-400">Progress</span>
                                    <span class="font-mono font-bold text-white">{{ $task->progress }}%</span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ $task->progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-xs text-slate-500 py-6">Belum ada tugas yang ditugaskan kepada Anda saat ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- TUGAS SIAP DIKLAIM (AVAILABLE TO CLAIM) -->
            @if(isset($claimableTasks) && $claimableTasks->count() > 0)
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-indigo-500/30">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Klaim Tugas Tersedia (Unassigned Pool)
                    </h2>
                    <span class="text-[11px] text-emerald-400 font-semibold">{{ $claimableTasks->count() }} tugas terbuka</span>
                </div>

                <div class="space-y-2.5">
                    @foreach($claimableTasks as $ct)
                        <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-white truncate">{{ $ct->title }}</p>
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase rounded bg-slate-800 text-slate-300">
                                        {{ $ct->priority }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    Proyek: <span class="text-slate-300 font-medium">{{ $ct->project ? $ct->project->name : 'General' }}</span>
                                    @if($ct->deadline) • Deadline: <span class="text-slate-300 font-mono">{{ $ct->deadline->format('d M Y') }}</span> @endif
                                    • Est: {{ $ct->estimated_hours }}h
                                </p>
                            </div>

                            <form action="{{ route('tasks.claim', $ct->id) }}" method="POST" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 flex items-center gap-1 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Klaim Tugas
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- RECENT DAILY PROGRESS HISTORY (Section L - Histori) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        My Daily Progress History (Never Overwritten)
                    </h2>
                    <a href="{{ route('progress.index') }}" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold">Full Log &rarr;</a>
                </div>

                <div class="space-y-3">
                    @forelse($myRecentProgress as $prog)
                        <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 text-xs">
                            <div class="flex items-center justify-between text-slate-400 mb-1">
                                <span class="font-bold text-slate-200 font-mono">{{ $prog->date->format('d M Y') }}</span>
                                <span class="font-semibold text-indigo-400">{{ $prog->progress }}% completed • {{ $prog->working_hours }}h logged</span>
                            </div>
                            <p class="text-slate-300"><strong class="text-slate-400">Completed:</strong> {{ $prog->completed_work }}</p>
                            <p class="text-slate-300 mt-0.5"><strong class="text-slate-400">Next Plan:</strong> {{ $prog->next_plan }}</p>
                            @if($prog->blocker)
                                <p class="text-rose-400 mt-1 font-medium"><strong class="text-rose-300">Blocker:</strong> {{ $prog->blocker }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-xs text-slate-500 py-4">No daily progress entries recorded yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- RIGHT 1 COL: Assigned Assets, Announcements, Schedule -->
        <div class="space-y-6">

            <!-- ASSIGNED ASSETS (Section AA & BR) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        My Assigned Hardware
                    </h3>
                    <span class="text-[11px] text-slate-400">{{ $myAssets->count() }} items</span>
                </div>

                <div class="space-y-2">
                    @forelse($myAssets as $asset)
                        <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-indigo-400 text-[11px]">{{ $asset->asset_tag }}</span>
                                <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-slate-800 text-slate-300">
                                    {{ $asset->condition }}
                                </span>
                            </div>
                            <p class="font-bold text-white mt-1">{{ $asset->brand }} {{ $asset->model }}</p>
                            <p class="text-[10px] text-slate-400">{{ $asset->specs ?? 'Standard Equipment' }}</p>
                        </div>
                    @empty
                        <p class="text-center text-xs text-slate-500 py-3">No physical assets currently checked out.</p>
                    @endforelse
                </div>
            </div>

            <!-- BROADCAST ANNOUNCEMENTS (Section AV) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        Company Announcements
                    </h3>
                    <a href="{{ route('communication.announcements') }}" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold">View All &rarr;</a>
                </div>

                <div class="space-y-2">
                    @forelse($announcements as $ann)
                        <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800/80 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-bold uppercase text-indigo-400">{{ $ann->category }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $ann->publish_date->format('d M') }}</span>
                            </div>
                            <p class="font-bold text-white mt-0.5">{{ $ann->title }}</p>
                            <p class="text-slate-400 text-[11px] line-clamp-2 mt-0.5">{{ $ann->content }}</p>
                        </div>
                    @empty
                        <p class="text-center text-xs text-slate-500 py-3">No announcements at this time.</p>
                    @endforelse
                </div>
            </div>

            <!-- MY SCHEDULE -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        My Upcoming Schedule
                    </h3>
                    <a href="{{ route('schedule.index') }}" class="text-[11px] text-sky-400 hover:text-sky-300 font-semibold">Agenda &rarr;</a>
                </div>
                <div class="space-y-2">
                    @forelse($upcomingEvents as $ev)
                        <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800/60 flex items-center justify-between text-xs">
                            <div class="min-w-0 pr-2">
                                <p class="font-semibold text-slate-200 truncate">{{ $ev['title'] }}</p>
                                <p class="text-[10px] text-slate-400">{{ $ev['context'] }}</p>
                            </div>
                            <span class="text-[10px] font-mono font-bold text-slate-300 bg-slate-800 px-2 py-0.5 rounded">
                                {{ \Carbon\Carbon::parse($ev['date'])->format('d M') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-2">No deadlines upcoming.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- ONE-TAP DAILY PROGRESS SUBMIT MODAL (Section BN: Mobile Daily Progress Flow) -->
    <div x-cloak x-show="progressModal" class="relative z-50" role="dialog" aria-modal="true">
        <div x-show="progressModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="progressModal = false"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto p-4 flex justify-center items-center">
            <div x-show="progressModal" x-transition class="w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white flex items-center gap-2">
                        <span class="p-1 rounded-lg bg-indigo-500/20 text-indigo-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </span>
                        Submit Daily Progress
                    </h3>
                    <button @click="progressModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form action="{{ route('progress.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Project *</label>
                        <select name="project_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            @foreach($myProjects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->name }} ({{ $proj->project_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Task (Optional)</label>
                        <select name="task_id" x-model="selectedTask" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            <option value="">General Project Work</option>
                            @foreach($myTasks as $t)
                                <option value="{{ $t->id }}">{{ $t->title }} ({{ $t->project ? $t->project->name : '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Date *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Progress % *</label>
                            <input type="number" name="progress" min="0" max="100" value="50" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Hours Spent *</label>
                        <input type="number" name="working_hours" step="0.5" min="0.5" max="24" value="8.0" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Completed Today *</label>
                        <textarea name="completed_work" rows="2" required placeholder="What features or tasks did you finish?" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Next Plan *</label>
                        <textarea name="next_plan" rows="2" required placeholder="What are you tackling tomorrow?" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-rose-300 mb-1">Blocker (If Any)</label>
                        <input type="text" name="blocker" placeholder="Any roadblock or dependency stopping you?" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-rose-900/50 text-white focus:outline-none focus:border-rose-500">
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="progressModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-lg shadow-indigo-600/30">Submit Progress</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ONE-TAP BLOCKER REPORT MODAL -->
    <div x-cloak x-show="blockerModal" class="relative z-50" role="dialog" aria-modal="true">
        <div x-show="blockerModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="blockerModal = false"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto p-4 flex justify-center items-center">
            <div x-show="blockerModal" x-transition class="w-full max-w-lg rounded-2xl bg-slate-900 border border-rose-500/40 p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white flex items-center gap-2">
                        <span class="p-1 rounded-lg bg-rose-500/20 text-rose-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </span>
                        Report Operational Blocker
                    </h3>
                    <button @click="blockerModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form action="{{ route('blockers.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Project *</label>
                        <select name="project_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                            @foreach($myProjects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Task Affected (Optional)</label>
                        <select name="task_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                            <option value="">None</option>
                            @foreach($myTasks as $t)
                                <option value="{{ $t->id }}">{{ $t->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Blocker Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Waiting for role structure or API key" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Priority *</label>
                            <select name="priority" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                                <option value="urgent">Urgent</option>
                                <option value="high" selected>High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Type *</label>
                            <select name="type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                                <option value="technical">Technical</option>
                                <option value="resource">Resource</option>
                                <option value="dependency">Dependency</option>
                                <option value="client">Client</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Detailed Description *</label>
                        <textarea name="description" rows="3" required placeholder="Describe what is blocking and what action is required to unblock" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="blockerModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold shadow-lg shadow-rose-600/30">Submit Blocker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
