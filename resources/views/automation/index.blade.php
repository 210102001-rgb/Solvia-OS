@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Automation Engine
                <span class="text-xs bg-cyan-500/20 text-cyan-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $rules->count() }} rules</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Trigger → Condition → Action. Rules run on schedule and can be triggered manually.</p>
        </div>
        <form action="{{ route('automation.run') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-xs font-semibold text-white shadow-md shadow-cyan-600/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Run All Automations
            </button>
        </form>
    </div>

    <!-- INFO BANNER -->
    <div class="p-4 rounded-2xl bg-indigo-950/40 border border-indigo-800/50 flex items-start gap-3">
        <svg class="w-5 h-5 text-indigo-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="text-xs text-indigo-200/80">
            <p class="font-bold text-indigo-300 mb-0.5">How Automations Work</p>
            Automation rules scan system data and trigger notifications when conditions are met — domain expiring, inventory low, task overdue, daily progress missing, or purchase approved. Toggle rules on/off without deleting them.
        </div>
    </div>

    <!-- AUTOMATION RULES GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($rules as $rule)
        @php
            $triggerIcon = match($rule->trigger_event ?? '') {
                'domain_expiring'       => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9',
                'inventory_low'         => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'progress_missing'      => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'task_overdue'          => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'purchase_approved'     => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                default                 => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
            };
        @endphp
        <div class="p-5 rounded-2xl bg-slate-900/90 border {{ $rule->is_active ? 'border-slate-800' : 'border-slate-800/40 opacity-60' }} shadow-md">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-{{ $rule->is_active ? 'cyan' : 'slate' }}-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $triggerIcon }}"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-white">{{ $rule->name }}</h3>
                        <span class="text-[10px] font-mono text-slate-500">{{ $rule->trigger_event }}</span>
                    </div>
                </div>

                <!-- Toggle Switch -->
                <form action="{{ route('automation.toggle', $rule->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $rule->is_active ? 'bg-cyan-600' : 'bg-slate-700' }}"
                        title="{{ $rule->is_active ? 'Click to disable' : 'Click to enable' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $rule->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </form>
            </div>

            @php
                $actionConfig = is_array($rule->action_config) ? $rule->action_config : (json_decode($rule->action_config ?? '{}', true) ?? []);
                $conditionConfig = is_array($rule->condition_config) ? $rule->condition_config : (json_decode($rule->condition_config ?? '{}', true) ?? []);
            @endphp

            @if(!empty($actionConfig['message']))
            <p class="text-xs text-slate-400 mb-4 bg-slate-950/60 p-3 rounded-xl border border-slate-800">{{ $actionConfig['message'] }}</p>
            @endif

            <div class="flex items-center justify-between pt-3 border-t border-slate-800/80 text-xs">
                <div class="text-slate-500">
                    Last triggered:
                    <span class="text-slate-300">{{ $rule->last_triggered_at ? \Carbon\Carbon::parse($rule->last_triggered_at)->diffForHumans() : 'Never' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $rule->is_active ? 'bg-cyan-500/20 text-cyan-300' : 'bg-slate-800 text-slate-500' }}">
                        {{ $rule->is_active ? 'Active' : 'Disabled' }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold font-mono bg-slate-800 text-slate-400">{{ $rule->logs_count ?? 0 }} runs</span>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            No automation rules configured.
        </div>
        @endforelse
    </div>

    <!-- AUTOMATION EXECUTION LOGS -->
    <div>
        <h2 class="text-sm font-bold text-white uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Recent Execution Log
        </h2>
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800 text-xs">
                    <thead class="bg-slate-950/80">
                        <tr>
                            <th class="px-5 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Timestamp</th>
                            <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Rule</th>
                            <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Trigger</th>
                            <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Result</th>
                            <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Message</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-5 py-3 font-mono text-slate-400">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-200 font-semibold">{{ $log->rule?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono text-slate-400">{{ $log->trigger_event }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $log->status === 'success' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 max-w-xs truncate">{{ $log->message }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No automation logs yet. Run automation rules to see execution history.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-800 px-5 py-3">{{ $logs->links() }}</div>
        </div>
    </div>

</div>
@endsection
