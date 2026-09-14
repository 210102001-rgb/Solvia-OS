@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- COMMAND CENTER TOP HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Command Center</h1>
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Live System</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Single source of truth • Full company situational awareness in under 60 seconds.</p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('schedule.reminders') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs font-semibold text-slate-200 transition">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Sync Schedule Engine
                </button>
            </form>
            <a href="{{ route('projects.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Project
            </a>
        </div>
    </div>

    <!-- 1. ACTION REQUIRED BANNER (High Priority Queue) -->
    @php
        $actionRequiredTotal = $pendingApprovals->count() + $openBlockers->count() + count($missingProgressUsers) + $overdueInvoices->count();
    @endphp
    @if($actionRequiredTotal > 0)
    <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-950/40 via-slate-900 to-rose-950/30 border border-amber-500/30 shadow-xl">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-amber-500/20 text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <h2 class="text-sm font-bold text-white uppercase tracking-wider">Action Required Today ({{ $actionRequiredTotal }} items)</h2>
            </div>
            <span class="text-xs text-amber-300/80 font-medium">Immediate Super Admin Review Needed</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            @if($pendingApprovals->count() > 0)
                <a href="{{ route('purchasing.index') }}" class="p-3 rounded-xl bg-slate-900/90 border border-amber-500/30 hover:border-amber-400 transition flex items-center justify-between">
                    <div>
                        <p class="font-bold text-amber-300">{{ $pendingApprovals->count() }} Purchase Request(s)</p>
                        <p class="text-[11px] text-slate-400">Awaiting approval</p>
                    </div>
                    <span class="px-2 py-1 rounded bg-amber-500/20 text-amber-300 font-bold text-[10px]">Review</span>
                </a>
            @endif

            @if($openBlockers->count() > 0)
                <a href="{{ route('blockers.index') }}" class="p-3 rounded-xl bg-slate-900/90 border border-rose-500/30 hover:border-rose-400 transition flex items-center justify-between">
                    <div>
                        <p class="font-bold text-rose-400">{{ $openBlockers->count() }} Active Blocker(s)</p>
                        <p class="text-[11px] text-slate-400">Impacting project timeline</p>
                    </div>
                    <span class="px-2 py-1 rounded bg-rose-500/20 text-rose-300 font-bold text-[10px]">Resolve</span>
                </a>
            @endif

            @if(count($missingProgressUsers) > 0)
                <a href="{{ route('progress.index') }}" class="p-3 rounded-xl bg-slate-900/90 border border-amber-500/30 hover:border-amber-400 transition flex items-center justify-between">
                    <div>
                        <p class="font-bold text-amber-300">{{ count($missingProgressUsers) }} Progress Missing</p>
                        <p class="text-[11px] text-slate-400">Executors haven't submitted</p>
                    </div>
                    <span class="px-2 py-1 rounded bg-amber-500/20 text-amber-300 font-bold text-[10px]">View</span>
                </a>
            @endif

            @if($overdueInvoices->count() > 0)
                <a href="{{ route('finance.invoices') }}" class="p-3 rounded-xl bg-slate-900/90 border border-rose-500/30 hover:border-rose-400 transition flex items-center justify-between">
                    <div>
                        <p class="font-bold text-rose-400">{{ $overdueInvoices->count() }} Overdue Invoices</p>
                        <p class="text-[11px] text-slate-400">Pending client payment</p>
                    </div>
                    <span class="px-2 py-1 rounded bg-rose-500/20 text-rose-300 font-bold text-[10px]">Collect</span>
                </a>
            @endif
        </div>
    </div>
    @endif

    <!-- 2. EXECUTIVE KPI CARDS (Financial & Operations) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Revenue -->
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Revenue</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-lg sm:text-2xl font-black text-white">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-emerald-400 mt-1 flex items-center gap-1 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                Total Incomes Inflow
            </p>
        </div>

        <!-- Expense -->
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Expenses</span>
                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
            </div>
            <div class="text-lg sm:text-2xl font-black text-white">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-rose-400 mt-1 flex items-center gap-1 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                Total Operational Costs
            </p>
        </div>

        <!-- Net Profit -->
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Net Profit</span>
                <span class="w-2 h-2 rounded-full {{ $netProfit >= 0 ? 'bg-indigo-400' : 'bg-rose-400' }}"></span>
            </div>
            <div class="text-lg sm:text-2xl font-black text-white">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </div>
            <p class="text-[11px] {{ $netProfit >= 0 ? 'text-indigo-400' : 'text-rose-400' }} mt-1 font-medium">
                Margin: {{ $profitMargin }}%
            </p>
        </div>

        <!-- Project Status Breakdown -->
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Projects Health</span>
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-lg sm:text-2xl font-black text-white">{{ $activeProjectsCount }}</span>
                <span class="text-xs text-slate-400">Active</span>
            </div>
            <div class="flex items-center gap-2 mt-2 text-[11px]">
                <span class="text-emerald-400 font-bold">{{ $onTrackProjectsCount }} Track</span>
                <span class="text-slate-600">•</span>
                <span class="text-amber-400 font-bold">{{ $atRiskProjectsCount }} Risk</span>
                <span class="text-slate-600">•</span>
                <span class="text-rose-400 font-bold">{{ $offTrackProjectsCount }} Off</span>
            </div>
        </div>
    </div>

    <!-- 3. MAIN COMMAND SPLIT (Left: Projects & Health Engine, Right: Team Workload & Infrastructure) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT 2 COLS: Active Projects Health Monitor -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Project Health Monitor</h2>
                </div>
                <a href="{{ route('projects.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">View All &rarr;</a>
            </div>

            <div class="space-y-3">
                @forelse($projects as $p)
                    @php
                        $healthBadge = match($p->health) {
                            'off_track' => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
                            'at_risk' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                            default => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                        };
                        $avgProgress = (int) round($p->tasks->avg('progress') ?? 0);
                    @endphp
                    <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono font-bold text-slate-400">{{ $p->project_code }}</span>
                                    <h3 class="font-bold text-sm text-white truncate">{{ $p->name }}</h3>
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full border {{ $healthBadge }}">
                                        {{ str_replace('_', ' ', $p->health) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">Client: <span class="text-slate-200">{{ $p->client ? $p->client->name : 'N/A' }}</span> • Deadline: <span class="text-slate-200 font-mono">{{ $p->deadline->format('d M Y') }}</span></p>
                            </div>

                            <a href="{{ route('projects.show', $p->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 transition text-center sm:text-right">
                                Open Workspace
                            </a>
                        </div>

                        <!-- Progress Bar & Metrics -->
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400 font-medium">Task Progress</span>
                                <span class="font-mono font-bold text-white">{{ $avgProgress }}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full {{ $p->health === 'off_track' ? 'bg-rose-500' : ($p->health === 'at_risk' ? 'bg-amber-500' : 'bg-emerald-500') }} transition-all duration-500"
                                     style="width: {{ $avgProgress }}%"></div>
                            </div>
                        </div>

                        <!-- Financial Health Tag & Stats -->
                        <div class="mt-3 pt-3 border-t border-slate-800/60 flex flex-wrap items-center justify-between text-xs text-slate-400 gap-2">
                            <div class="flex items-center gap-4">
                                <span>Revenue: <strong class="text-slate-200 font-mono">Rp {{ number_format($p->revenue, 0, ',', '.') }}</strong></span>
                                <span>Actual Cost: <strong class="text-slate-200 font-mono">Rp {{ number_format($p->actual_cost, 0, ',', '.') }}</strong></span>
                                <span>Profit: <strong class="{{ $p->profit >= 0 ? 'text-emerald-400' : 'text-rose-400' }} font-mono">Rp {{ number_format($p->profit, 0, ',', '.') }}</strong></span>
                            </div>
                            <div>
                                <span class="text-slate-400">{{ $p->tasks->count() }} Tasks ({{ $p->tasks->where('status', 'done')->count() }} Done)</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-400">
                        No active projects registered yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT 1 COL: Team Workload & Infrastructure Expiries -->
        <div class="space-y-6">

            <!-- TEAM WORKLOAD ENGINE (Section O) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Team Capacity & Workload</h3>
                    </div>
                    <span class="text-[11px] font-bold {{ $overloadedCount > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                        {{ $overloadedCount }} Overloaded
                    </span>
                </div>

                <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                    @foreach($teamWorkloads as $load)
                        @php
                            $userObj = $load['user'];
                            $loadBadge = match($load['status']) {
                                'overloaded' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                'high' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                default => 'bg-slate-800 text-slate-300 border-slate-700',
                            };
                        @endphp
                        <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800/80 flex items-center justify-between text-xs">
                            <div class="min-w-0 flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-600/80 flex items-center justify-center font-bold text-[10px] text-white flex-shrink-0">
                                    {{ strtoupper(substr($userObj->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-white truncate">{{ $userObj->name }}</p>
                                    <p class="text-[10px] text-slate-400 truncate capitalize">{{ str_replace('_', ' ', $userObj->role) }}</p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded border {{ $loadBadge }}">
                                    {{ $load['status'] }}
                                </span>
                                <p class="text-[10px] font-mono text-slate-400 mt-1">{{ $load['active_tasks_count'] }} tasks • {{ $load['estimated_hours'] }}h</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- INFRASTRUCTURE EXPIRIES & SUBSCRIPTIONS (Section BS & CM) -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Upcoming Expiry & Billing</h3>
                    </div>
                    <a href="{{ route('resources.infrastructure') }}" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold">Registry &rarr;</a>
                </div>

                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    @forelse($expiringInfra as $inf)
                        @php
                            $targetDate = $inf->expiry_date ?? $inf->next_billing_date;
                            $daysLeft = \Carbon\Carbon::today()->diffInDays($targetDate, false);
                        @endphp
                        <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800/80 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[9px] font-extrabold uppercase tracking-wider text-indigo-400 block">{{ $inf->type }}</span>
                                <p class="font-bold text-white">{{ $inf->name }}</p>
                                <p class="text-[10px] text-slate-400">{{ $inf->provider }} • {{ $targetDate->format('d M Y') }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded {{ $daysLeft <= 7 ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300' }}">
                                {{ $daysLeft }}d left
                            </span>
                        </div>
                    @empty
                        <p class="text-center text-xs text-slate-500 py-3">No infrastructure expiring within 30 days.</p>
                    @endforelse

                    @foreach($expiringSubscriptions as $sub)
                        @php $daysSub = \Carbon\Carbon::today()->diffInDays($sub->next_billing_date, false); @endphp
                        <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800/80 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[9px] font-extrabold uppercase tracking-wider text-teal-400 block">Subscription</span>
                                <p class="font-bold text-white">{{ $sub->provider }} ({{ $sub->plan_name }})</p>
                                <p class="text-[10px] text-slate-400">Rp {{ number_format($sub->cost, 0, ',', '.') }} • {{ $sub->next_billing_date->format('d M Y') }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded bg-teal-500/20 text-teal-300">
                                {{ $daysSub }}d left
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- UNIFIED UPCOMING SCHEDULE GLANCE -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800/80">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Schedule Engine (Next 14 Days)
                    </h3>
                    <a href="{{ route('schedule.index') }}" class="text-[11px] text-sky-400 hover:text-sky-300 font-semibold">Calendar &rarr;</a>
                </div>
                <div class="space-y-2">
                    @forelse($upcomingEvents as $evt)
                        <div class="p-2 rounded-xl bg-slate-950/60 border border-slate-800/60 flex items-center justify-between text-xs">
                            <div class="min-w-0 pr-2">
                                <p class="font-semibold text-slate-200 truncate">{{ $evt['title'] }}</p>
                                <p class="text-[10px] text-slate-400">{{ $evt['category'] }} • {{ $evt['context'] }}</p>
                            </div>
                            <span class="text-[10px] font-mono font-bold text-slate-300 bg-slate-800 px-2 py-0.5 rounded flex-shrink-0">
                                {{ \Carbon\Carbon::parse($evt['date'])->format('d M') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-2">No pending schedule items.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
