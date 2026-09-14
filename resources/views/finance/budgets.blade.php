@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ budgetModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Budget Planner
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $budgets->count() }} entries</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Define, track, and analyze budget vs. actuals across company, projects, and categories</p>
        </div>
        <button @click="budgetModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Define Budget
        </button>
    </div>

    <!-- SUMMARY KPIs -->
    @php
        $totalBudgeted = $budgets->sum('budgeted_amount');
        $totalActual = $budgets->sum('actual_amount');
        $totalVariance = $totalBudgeted - $totalActual;
        $utilizationPct = $totalBudgeted > 0 ? round(($totalActual / $totalBudgeted) * 100, 1) : 0;
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Budgeted</span>
            <p class="text-xl font-black text-white font-mono mt-1">Rp {{ number_format($totalBudgeted, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Actual</span>
            <p class="text-xl font-black text-rose-400 font-mono mt-1">Rp {{ number_format($totalActual, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Variance</span>
            <p class="text-xl font-black {{ $totalVariance >= 0 ? 'text-emerald-400' : 'text-rose-400' }} font-mono mt-1">
                {{ $totalVariance >= 0 ? '+' : '' }}Rp {{ number_format($totalVariance, 0, ',', '.') }}
            </p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Utilization</span>
            <p class="text-xl font-black {{ $utilizationPct > 100 ? 'text-rose-400' : ($utilizationPct > 85 ? 'text-amber-400' : 'text-indigo-400') }} font-mono mt-1">{{ $utilizationPct }}%</p>
        </div>
    </div>

    <!-- BUDGET TABLE -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-xs">
                <thead class="bg-slate-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Scope</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Period</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-400 uppercase tracking-wider">Budgeted</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-400 uppercase tracking-wider">Actual</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-400 uppercase tracking-wider">Variance</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider w-36">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($budgets as $budget)
                    @php
                        $variance = $budget->budgeted_amount - $budget->actual_amount;
                        $pct = $budget->budgeted_amount > 0 ? min(round(($budget->actual_amount / $budget->budgeted_amount) * 100, 1), 100) : 0;
                        $barColor = $pct > 100 ? 'bg-rose-500' : ($pct > 85 ? 'bg-amber-500' : 'bg-indigo-500');
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $budget->scope_type === 'project' ? 'bg-indigo-500/20 text-indigo-300' : ($budget->scope_type === 'company' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800 text-slate-300') }}">
                                {{ $budget->scope_type }}
                            </span>
                            @if($budget->project)<p class="text-slate-400 text-[10px] mt-0.5">{{ $budget->project->name }}</p>@endif
                        </td>
                        <td class="px-4 py-3.5 font-mono text-slate-300">
                            {{ $budget->month ? str_pad($budget->month, 2, '0', STR_PAD_LEFT) . '/' : '' }}{{ $budget->year }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-300 capitalize">{{ $budget->category ?? '—' }}</td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-200">Rp {{ number_format($budget->budgeted_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-200">Rp {{ number_format($budget->actual_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3.5 text-right font-mono font-bold {{ $variance >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $variance >= 0 ? '+' : '' }}Rp {{ number_format($variance, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="space-y-1">
                                <div class="flex justify-between text-[9px]">
                                    <span class="text-slate-500">{{ $pct }}%</span>
                                    @if($pct > 100)<span class="text-rose-400 font-bold">Exceeded</span>@endif
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-950 overflow-hidden">
                                    <div class="h-full rounded-full {{ $barColor }} transition-all" style="width: {{ min($pct, 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">No budget entries defined yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: DEFINE BUDGET -->
    <div x-cloak x-show="budgetModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="budgetModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="budgetModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="budgetModal = false">
            <div x-show="budgetModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto" x-data="{ scopeType: 'company' }">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Define Budget Allocation</h3>
                    <button @click="budgetModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('finance.budgets.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Scope Type *</label>
                            <select name="scope_type" x-model="scopeType" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="company">Company-wide</option>
                                <option value="project">Project-specific</option>
                                <option value="category">By Category</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Year *</label>
                            <input type="number" name="year" value="{{ date('Y') }}" min="2020" max="2040" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div x-show="scopeType === 'project'">
                        <label class="block font-semibold text-slate-300 mb-1">Project</label>
                        <select name="project_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            <option value="">Select project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Month (optional)</label>
                            <select name="month" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="">Annual</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Category</label>
                            <select name="category" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="">All categories</option>
                                <option value="salary">Salary</option>
                                <option value="infrastructure">Infrastructure</option>
                                <option value="software">Software</option>
                                <option value="marketing">Marketing</option>
                                <option value="equipment">Equipment</option>
                                <option value="operational">Operational</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Budgeted Amount (Rp) *</label>
                        <input type="number" name="budgeted_amount" min="0" required placeholder="e.g. 50000000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="budgetModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/20">Save Budget</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
