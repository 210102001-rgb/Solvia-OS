@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ newBlockerModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Blockers & Roadblocks
                <span class="text-xs bg-rose-500/20 text-rose-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $blockers->total() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Directly feeds into Project Health Engine • Unblocking operational flow.</p>
        </div>

        <button @click="newBlockerModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs font-semibold text-white shadow-md shadow-rose-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Report Blocker
        </button>
    </div>

    <!-- BLOCKER CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($blockers as $b)
            @php
                $border = match($b->status) {
                    'resolved' => 'border-emerald-500/30 bg-emerald-950/10',
                    'in_progress' => 'border-amber-500/30 bg-amber-950/10',
                    default => 'border-rose-500/40 bg-rose-950/15',
                };
            @endphp
            <div class="p-5 rounded-2xl border {{ $border }} flex flex-col justify-between space-y-4 shadow-lg">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-rose-500/20 text-rose-300">
                            {{ $b->priority }} • {{ $b->type }}
                        </span>
                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-slate-800 text-slate-300">
                            {{ $b->status }}
                        </span>
                    </div>

                    <h3 class="font-bold text-base text-white leading-snug">{{ $b->title }}</h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Project: <strong class="text-slate-200">{{ $b->project ? $b->project->name : 'General' }}</strong>
                        @if($b->task) • Task: <span class="text-slate-300">{{ $b->task->title }}</span> @endif
                    </p>

                    <p class="text-xs text-slate-300 mt-3 bg-slate-950/60 p-3 rounded-xl border border-slate-800/80 leading-relaxed">
                        {{ $b->description }}
                    </p>

                    @if($b->status === 'resolved')
                        <div class="mt-3 p-3 rounded-xl bg-emerald-950/40 border border-emerald-800/50 text-xs text-emerald-300">
                            <span class="font-bold block text-[10px] uppercase tracking-wider text-emerald-400">Resolution</span>
                            {{ $b->resolution_notes }}
                        </div>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-800/80">
                    <div class="flex items-center justify-between text-[11px] text-slate-400 mb-2">
                        <span>Reported by: <strong class="text-slate-300">{{ $b->reporter ? $b->reporter->name : 'N/A' }}</strong></span>
                        <span class="font-mono">{{ $b->created_at->format('d M Y') }}</span>
                    </div>

                    @if($b->status !== 'resolved' && Auth::user()->isSuperAdmin())
                        <form action="{{ route('blockers.resolve', $b->id) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="resolution_notes" required placeholder="How was it resolved?" class="flex-1 px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-emerald-500">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex-shrink-0">
                                Resolve
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
                No active blockers! Your operations are clear.
            </div>
        @endforelse
    </div>

    <div class="pt-4">
        {{ $blockers->links() }}
    </div>

    <!-- MODAL: REPORT BLOCKER -->
    <div x-cloak x-show="newBlockerModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="newBlockerModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="newBlockerModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="newBlockerModal = false">
            <div x-show="newBlockerModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-rose-500/40 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Report Operational Blocker</h3>
                    <button @click="newBlockerModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <form action="{{ route('blockers.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Project *</label>
                        <select name="project_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->project_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Blocker Title *</label>
                        <input type="text" name="title" required placeholder="Brief title of the impediment" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Priority *</label>
                            <select name="priority" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                                <option value="urgent">Urgent</option>
                                <option value="high" selected>High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Type *</label>
                            <select name="type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none">
                                <option value="technical">Technical</option>
                                <option value="resource">Resource</option>
                                <option value="dependency">Dependency</option>
                                <option value="client">Client</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Detailed Explanation *</label>
                        <textarea name="description" rows="3" required placeholder="What is happening and how does it block progress?" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="newBlockerModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold">Submit Blocker</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
