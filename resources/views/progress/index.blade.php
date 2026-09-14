@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ submitModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Daily Progress Ledger
                <span class="text-xs bg-emerald-500/20 text-emerald-300 font-bold px-2 py-0.5 rounded-full font-mono">Immutable History</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Operational accountability • Preserving historical progress entries over time.</p>
        </div>

        <button @click="submitModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Submit Today's Progress
        </button>
    </div>

    <!-- FILTER BAR -->
    <form method="GET" action="{{ route('progress.index') }}" class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex flex-wrap items-center gap-3 text-xs">
        <div>
            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Filter Project</label>
            <select name="project_id" class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                <option value="">All Projects</option>
                @foreach($myProjects as $proj)
                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Filter Date</label>
            <input type="date" name="date" value="{{ request('date') }}" class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
        </div>

        <div class="self-end flex items-center gap-2">
            <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold">Filter</button>
            <a href="{{ route('progress.index') }}" class="px-3 py-1.5 text-slate-400 hover:text-white">Reset</a>
        </div>
    </form>

    <!-- PROGRESS TIMELINE CARDS (Section BK & BN: Mobile Adaptive Cards) -->
    <div class="space-y-3">
        @forelse($progressList as $item)
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80 hover:border-slate-700 transition space-y-3 shadow-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-xs text-white">
                            {{ strtoupper(substr($item->user->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-white text-sm">{{ $item->user->name }}</span>
                                <span class="text-[10px] text-indigo-400 font-medium capitalize">({{ str_replace('_', ' ', $item->user->role) }})</span>
                            </div>
                            <p class="text-xs text-slate-400">
                                Project: <strong class="text-slate-200">{{ $item->project ? $item->project->name : 'N/A' }}</strong>
                                @if($item->task) • Task: <span class="text-slate-300 font-medium">{{ $item->task->title }}</span> @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-right">
                        <div>
                            <span class="text-xs font-mono font-bold text-white">{{ $item->progress }}% progress</span>
                            <p class="text-[10px] text-slate-400 font-mono">{{ $item->working_hours }} hours logged</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-mono font-bold rounded-xl bg-slate-800 text-slate-300">
                            {{ $item->date->format('d M Y') }}
                        </span>
                    </div>
                </div>

                <!-- Completed & Next Plan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/60">
                        <span class="text-[10px] uppercase font-bold text-emerald-400 block mb-1">Completed Work</span>
                        <p class="text-slate-200 leading-relaxed">{{ $item->completed_work }}</p>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/60">
                        <span class="text-[10px] uppercase font-bold text-indigo-400 block mb-1">Next Plan</span>
                        <p class="text-slate-200 leading-relaxed">{{ $item->next_plan }}</p>
                    </div>
                </div>

                @if($item->blocker)
                    <div class="p-3 rounded-xl bg-rose-950/40 border border-rose-900/50 text-xs text-rose-300 flex items-start gap-2">
                        <svg class="w-4 h-4 text-rose-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <strong class="text-rose-200 font-bold">Reported Blocker:</strong> {{ $item->blocker }}
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-xs text-slate-500">
                No daily progress history recorded for this selection.
            </div>
        @endforelse

        <div class="pt-4">
            {{ $progressList->links() }}
        </div>
    </div>

    <!-- MODAL: SUBMIT PROGRESS -->
    <div x-cloak x-show="submitModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="submitModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="submitModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="submitModal = false">
            <div x-show="submitModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Log Daily Operational Progress</h3>
                    <button @click="submitModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                @if($myProjects->isEmpty())
                    <div class="mb-4 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs">
                        ⚠️ No active projects found. You need at least one active project to log daily progress. <a href="{{ route('projects.index') }}" class="underline font-bold text-white hover:text-indigo-300">View projects</a>.
                    </div>
                @endif

                <form action="{{ route('progress.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Project *</label>
                        <select name="project_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            @foreach($myProjects as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->name }} ({{ $pr->project_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Task (Optional)</label>
                        <select name="task_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            <option value="">General Project Work</option>
                            @foreach($myTasks as $tk)
                                <option value="{{ $tk->id }}">{{ $tk->title }} ({{ $tk->project ? $tk->project->name : '' }})</option>
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
                            <input type="number" name="progress" min="0" max="100" value="65" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Working Hours *</label>
                        <input type="number" name="working_hours" step="0.5" min="0.5" max="24" value="8.0" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Completed Today *</label>
                        <textarea name="completed_work" rows="2" required placeholder="What was achieved?" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Next Plan *</label>
                        <textarea name="next_plan" rows="2" required placeholder="What is the plan for tomorrow?" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-rose-300 mb-1">Blocker (If Any)</label>
                        <input type="text" name="blocker" placeholder="Any impediment stopping work?" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-rose-900/50 text-white focus:outline-none focus:border-rose-500">
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="submitModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold">Save Progress</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
