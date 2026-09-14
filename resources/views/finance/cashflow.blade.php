@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Cashflow Statement
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full uppercase">Real-time</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Cash In / Cash Out / Net position across all financial accounts</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.incomes') }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white transition">+ Income</a>
            <a href="{{ route('finance.expenses') }}" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs font-semibold text-white transition">+ Expense</a>
        </div>
    </div>

    <!-- NET CASHFLOW HERO -->
    <div class="p-6 rounded-2xl border {{ $net >= 0 ? 'bg-emerald-950/30 border-emerald-500/20' : 'bg-rose-950/30 border-rose-500/20' }} text-center">
        <p class="text-xs text-slate-400 uppercase font-bold tracking-widest mb-2">Net Cashflow Position</p>
        <p class="text-4xl sm:text-5xl font-black font-mono {{ $net >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
            {{ $net >= 0 ? '+' : '' }}Rp {{ number_format($net, 0, ',', '.') }}
        </p>
        <p class="text-xs text-slate-400 mt-3">
            <span class="text-emerald-400 font-bold">Rp {{ number_format($totalIn, 0, ',', '.') }} in</span>
            &nbsp;—&nbsp;
            <span class="text-rose-400 font-bold">Rp {{ number_format($totalOut, 0, ',', '.') }} out</span>
        </p>
    </div>

    <!-- KPI STRIP -->
    <div class="grid grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Cash In</span>
            <p class="text-xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-500 mt-1">{{ $incomes->count() }} income entries</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Cash Out</span>
            <p class="text-xl font-black text-rose-400 font-mono mt-1">Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-500 mt-1">{{ $expenses->count() }} expense entries</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block">Net</span>
            <p class="text-xl font-black {{ $net >= 0 ? 'text-indigo-400' : 'text-rose-400' }} font-mono mt-1">
                {{ $net >= 0 ? '+' : '' }}Rp {{ number_format($net, 0, ',', '.') }}
            </p>
            @php $margin = $totalIn > 0 ? round(($net / $totalIn) * 100, 1) : 0; @endphp
            <p class="text-[10px] text-slate-500 mt-1">{{ $margin }}% profit margin</p>
        </div>
    </div>

    <!-- CASHFLOW CHART — Real data, responsive (Section BT) -->
    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
        <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Monthly Cashflow Trend</h3>
        <canvas id="cashflowChart" height="80"></canvas>
    </div>

    <!-- TWO-COLUMN: RECENT IN / RECENT OUT -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- INCOME STREAM -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span> Income Stream
                </h3>
                <a href="{{ route('finance.incomes') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($incomes->sortByDesc('date')->take(8) as $income)
                <div class="px-5 py-3 flex items-center justify-between text-xs">
                    <div class="min-w-0">
                        <p class="font-semibold text-white truncate">{{ $income->source }}</p>
                        <p class="text-slate-500 text-[10px] font-mono">{{ $income->date->format('d M Y') }} • {{ $income->category }}</p>
                    </div>
                    <span class="font-mono font-bold text-emerald-400 ml-3 flex-shrink-0">+Rp {{ number_format($income->amount, 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-slate-500 text-xs">No income entries yet.</div>
                @endforelse
            </div>
        </div>

        <!-- EXPENSE STREAM -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-400 inline-block"></span> Expense Stream
                </h3>
                <a href="{{ route('finance.expenses') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">All &rarr;</a>
            </div>
            <div class="divide-y divide-slate-800">
                @forelse($expenses->sortByDesc('date')->take(8) as $expense)
                <div class="px-5 py-3 flex items-center justify-between text-xs">
                    <div class="min-w-0">
                        <p class="font-semibold text-white truncate">{{ $expense->notes ?? $expense->vendor ?? 'Operational Cost' }}</p>
                        <p class="text-slate-500 text-[10px] font-mono">{{ $expense->date->format('d M Y') }} • <span class="capitalize">{{ $expense->category }}</span></p>
                    </div>
                    <span class="font-mono font-bold text-rose-400 ml-3 flex-shrink-0">-Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-slate-500 text-xs">No expense entries yet.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
// Monthly cashflow chart — real data from backend
(function() {
    const incomes = @json($incomes->groupBy(fn($i) => $i->date->format('Y-m'))->map->sum('amount'));
    const expenses = @json($expenses->groupBy(fn($e) => $e->date->format('Y-m'))->map->sum('amount'));
    const allMonths = [...new Set([...Object.keys(incomes), ...Object.keys(expenses)])].sort();

    const labels = allMonths.map(m => {
        const [year, month] = m.split('-');
        return new Date(year, month - 1).toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
    });

    const ctx = document.getElementById('cashflowChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Cash In',
                    data: allMonths.map(m => incomes[m] || 0),
                    backgroundColor: 'rgba(52, 211, 153, 0.4)',
                    borderColor: '#34d399',
                    borderWidth: 1.5,
                    borderRadius: 6,
                },
                {
                    label: 'Cash Out',
                    data: allMonths.map(m => expenses[m] || 0),
                    backgroundColor: 'rgba(248, 113, 113, 0.4)',
                    borderColor: '#f87171',
                    borderWidth: 1.5,
                    borderRadius: 6,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { labels: { color: '#94a3b8', font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                    }
                }
            },
            scales: {
                x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: '#1e293b' } },
                y: {
                    ticks: {
                        color: '#64748b', font: { size: 10 },
                        callback: v => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v)
                    },
                    grid: { color: '#1e293b' }
                }
            }
        }
    });
})();
</script>
@endpush

@endsection
