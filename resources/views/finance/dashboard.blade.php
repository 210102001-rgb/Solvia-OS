@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER: FINANCIAL COMMAND CENTER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Financial Command Center
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Single Source of Truth</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Pusat Kendali Keuangan Perusahaan • Single Ledger, Cash Runway, Financial Health & Cashflow</p>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <a href="{{ route('finance.accounts') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Accounts & Transfer
            </a>
            <a href="{{ route('finance.incomes') }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white transition">
                + Record Income
            </a>
            <a href="{{ route('finance.expenses') }}" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs font-semibold text-white transition">
                + Record Expense
            </a>
            <a href="{{ route('finance.invoices') }}" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white transition">
                + Create Invoice
            </a>
        </div>
    </div>

    <!-- ROW 1: PRIMARY FINANCIAL KPIs (Revenue ≠ Cash In, Expense ≠ Cash Out, Profit ≠ Cash) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- TOTAL CASH -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total Cash & Bank</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </div>
            <p class="text-lg sm:text-2xl font-black text-white font-mono mt-1">Rp {{ number_format($metrics['total_cash'], 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                <span>Net Cashflow:</span>
                <span class="font-bold {{ $metrics['net_cashflow'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $metrics['net_cashflow'] >= 0 ? '+' : '' }}Rp {{ number_format($metrics['net_cashflow'], 0, ',', '.') }}
                </span>
            </p>
        </div>

        <!-- REVENUE -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total Revenue</span>
            <p class="text-lg sm:text-2xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($metrics['total_revenue'], 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-400 mt-1">Cash In: Rp {{ number_format($metrics['cash_in'], 0, ',', '.') }}</p>
        </div>

        <!-- EXPENSE -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total Expenses</span>
            <p class="text-lg sm:text-2xl font-black text-rose-400 font-mono mt-1">Rp {{ number_format($metrics['total_expense'], 0, ',', '.') }}</p>
            <p class="text-[10px] text-slate-400 mt-1">Cash Out: Rp {{ number_format($metrics['cash_out'], 0, ',', '.') }}</p>
        </div>

        <!-- NET PROFIT -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Net Profit</span>
                <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded {{ $metrics['net_profit'] >= 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                    {{ $metrics['profit_margin'] }}% Margin
                </span>
            </div>
            <p class="text-lg sm:text-2xl font-black {{ $metrics['net_profit'] >= 0 ? 'text-indigo-400' : 'text-rose-400' }} font-mono mt-1">
                Rp {{ number_format($metrics['net_profit'], 0, ',', '.') }}
            </p>
            <p class="text-[10px] text-slate-400 mt-1">{{ $metrics['net_profit'] >= 0 ? 'Operasional profitable' : 'Defisit operasional' }}</p>
        </div>
    </div>

    <!-- ROW 2: LIQUIDITY & CONTROL RADAR (Cash Runway, Financial Health, Receivables & Payables) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- CASH RUNWAY CARD -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Cash Runway
                    </h3>
                    <p class="text-[11px] text-slate-400">Kemampuan bertahan kas lancar</p>
                </div>
                @php
                    $runwayBadge = match($metrics['cash_runway_status']) {
                        'HEALTHY' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                        'WARNING' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                        default => 'bg-rose-500/20 text-rose-400 border-rose-500/30'
                    };
                @endphp
                <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-full border {{ $runwayBadge }}">
                    {{ $metrics['cash_runway_status'] }}
                </span>
            </div>

            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase block font-semibold">Estimasi Ketahanan Kas</span>
                    <span class="text-3xl font-black text-white font-mono">{{ $metrics['cash_runway_months'] }} <span class="text-sm font-medium text-slate-400">Bulan</span></span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 uppercase block font-semibold">Avg Burn Rate / Bln</span>
                    <span class="text-xs font-mono font-bold text-slate-300">Rp {{ number_format($metrics['avg_monthly_burn'], 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-400 text-[11px]">
                    <span>Monthly Recurring Cost (MRC)</span>
                    <span class="font-mono font-bold text-slate-200">Rp {{ number_format($metrics['mrc'], 0, ',', '.') }} / bln</span>
                </div>
                <div class="flex justify-between text-slate-400 text-[11px]">
                    <span>Annual Recurring Cost (ARC)</span>
                    <span class="font-mono font-bold text-slate-200">Rp {{ number_format($metrics['arc'], 0, ',', '.') }} / thn</span>
                </div>
            </div>
        </div>

        <!-- FINANCIAL HEALTH SCORECARD -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Financial Health Index
                    </h3>
                    <p class="text-[11px] text-slate-400">Skor Komposit Kesehatan Finansial</p>
                </div>
                <span class="text-xl font-black font-mono text-indigo-400">
                    {{ $metrics['health_score']['total_score'] }}<span class="text-xs text-slate-500">/100</span>
                </span>
            </div>

            <!-- Pillars breakdown -->
            <div class="space-y-2 pt-1">
                @php
                    $pillars = [
                        ['label' => 'Cash Position & Runway', 'val' => $metrics['health_score']['pillars']['cash_position'], 'max' => 25],
                        ['label' => 'Profitability & Margin', 'val' => $metrics['health_score']['pillars']['profitability'], 'max' => 25],
                        ['label' => 'Liquidity Ratio', 'val' => $metrics['health_score']['pillars']['liquidity'], 'max' => 15],
                        ['label' => 'Receivable Risk Control', 'val' => $metrics['health_score']['pillars']['receivable_risk'], 'max' => 15],
                        ['label' => 'Payable Risk Control', 'val' => $metrics['health_score']['pillars']['payable_risk'], 'max' => 10],
                        ['label' => 'Budget Control', 'val' => $metrics['health_score']['pillars']['budget_control'], 'max' => 10],
                    ];
                @endphp
                @foreach($pillars as $p)
                <div class="text-[10px]">
                    <div class="flex justify-between text-slate-400 mb-1">
                        <span>{{ $p['label'] }}</span>
                        <span class="font-mono font-semibold text-slate-300">{{ $p['val'] }} / {{ $p['max'] }}</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-400 rounded-full" style="width: {{ ($p['val'] / $p['max']) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- RECEIVABLES & PAYABLES SUMMARY -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center justify-between">
                <span>Working Capital Summary</span>
                <span class="text-[10px] text-slate-400 font-normal">Piutang & Hutang</span>
            </h3>

            <!-- Piutang Card -->
            <a href="{{ route('finance.receivables') }}" class="block p-3.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-cyan-500/40 transition">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        Total Piutang (Receivables)
                    </span>
                    <span class="font-mono font-bold text-white">Rp {{ number_format($metrics['total_receivable'], 0, ',', '.') }}</span>
                </div>
                @if($metrics['overdue_receivable'] > 0)
                <p class="text-[10px] text-rose-400 mt-1.5 font-medium flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Overdue Piutang: Rp {{ number_format($metrics['overdue_receivable'], 0, ',', '.') }}
                </p>
                @else
                <p class="text-[10px] text-emerald-400 mt-1 font-medium">Semua piutang lancar</p>
                @endif
            </a>

            <!-- Hutang Card -->
            <a href="{{ route('finance.payables') }}" class="block p-3.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-orange-500/40 transition">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                        Total Hutang (Payables)
                    </span>
                    <span class="font-mono font-bold text-white">Rp {{ number_format($metrics['total_payable'], 0, ',', '.') }}</span>
                </div>
                @if($metrics['overdue_payable'] > 0)
                <p class="text-[10px] text-rose-400 mt-1.5 font-medium flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Jatuh Tempo: Rp {{ number_format($metrics['overdue_payable'], 0, ',', '.') }}
                </p>
                @else
                <p class="text-[10px] text-slate-400 mt-1">Kewajiban vendor aman</p>
                @endif
            </a>

            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-800">
                <span class="text-slate-400">Net Working Capital</span>
                <span class="font-mono font-bold {{ ($metrics['total_cash'] + $metrics['total_receivable'] - $metrics['total_payable']) >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    Rp {{ number_format($metrics['total_cash'] + $metrics['total_receivable'] - $metrics['total_payable'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <!-- ROW 3: ACTION REQUIRED ALERTS -->
    @if(count($metrics['actions']) > 0)
    <div class="p-5 rounded-2xl bg-amber-950/20 border border-amber-500/30 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-amber-300 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Action Required — Financial Attention Needed
            </h3>
            <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 font-bold text-[10px]">
                {{ count($metrics['actions']) }} Items
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach($metrics['actions'] as $act)
            <a href="{{ $act['link'] }}" class="p-3 rounded-xl bg-slate-900/90 border border-amber-500/20 hover:border-amber-400/50 transition flex items-center justify-between text-xs">
                <div>
                    <span class="font-bold text-white block">{{ $act['title'] }}</span>
                    <span class="text-[11px] text-slate-400 mt-0.5 block">{{ $act['subtitle'] }}</span>
                </div>
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- ROW 4: ACCOUNTS & RECENT LEDGER TRANSACTIONS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- CORPORATE FINANCIAL ACCOUNTS -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Corporate Financial Accounts
                </h3>
                <a href="{{ route('finance.accounts') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">Kelola Rekening &rarr;</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($accounts as $acc)
                <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[9px] uppercase font-bold text-indigo-400 font-mono">{{ $acc->account_code }}</span>
                        <span class="px-1.5 py-0.5 text-[8px] font-bold rounded bg-slate-800 text-slate-300 uppercase">{{ $acc->account_type ?? 'BANK' }}</span>
                    </div>
                    <p class="font-bold text-white text-sm mt-1">{{ $acc->account_name }}</p>
                    <p class="text-[10px] text-slate-400">{{ $acc->provider ?? $acc->bank_name }} • {{ $acc->account_number }}</p>
                    <p class="font-mono font-bold text-emerald-400 text-sm mt-2">
                        Rp {{ number_format($acc->balance, 0, ',', '.') }}
                    </p>
                </div>
                @empty
                <div class="p-6 text-center text-slate-500 text-xs col-span-2">Belum ada rekening terdaftar.</div>
                @endforelse
            </div>
        </div>

        <!-- RECENT MASTER LEDGER TRANSACTIONS -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Recent Master Ledger Transactions
                </h3>
                <a href="{{ route('finance.transactions') }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">Lihat Semua Ledger &rarr;</a>
            </div>

            <div class="space-y-2">
                @forelse($recentTransactions as $tx)
                @php
                    $typeColor = match($tx->transaction_type) {
                        'INCOME' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                        'EXPENSE' => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
                        'TRANSFER' => 'bg-sky-500/20 text-sky-400 border-sky-500/30',
                        'CAPITAL_IN' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
                        'CAPITAL_OUT' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                        default => 'bg-slate-700/40 text-slate-300 border-slate-600/30'
                    };
                @endphp
                <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="px-1.5 py-0.5 text-[8px] font-black uppercase rounded border {{ $typeColor }}">
                                {{ $tx->transaction_type }}
                            </span>
                            <span class="font-mono text-[10px] text-slate-400">{{ $tx->transaction_code }}</span>
                            <span class="text-[10px] text-slate-500">{{ $tx->transaction_date ? $tx->transaction_date->format('d M') : '' }}</span>
                        </div>
                        <p class="font-medium text-white text-xs truncate max-w-xs">{{ $tx->description }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-bold text-xs {{ in_array($tx->transaction_type, ['INCOME', 'CAPITAL_IN', 'OPENING_BALANCE']) ? 'text-emerald-400' : (in_array($tx->transaction_type, ['EXPENSE', 'CAPITAL_OUT']) ? 'text-rose-400' : 'text-slate-300') }}">
                            {{ in_array($tx->transaction_type, ['INCOME', 'CAPITAL_IN', 'OPENING_BALANCE']) ? '+' : (in_array($tx->transaction_type, ['EXPENSE', 'CAPITAL_OUT']) ? '-' : '') }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </span>
                        <span class="text-[9px] text-slate-500 block">{{ $tx->account->account_name ?? 'Ledger' }}</span>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-slate-500 text-xs">Belum ada transaksi tercatat.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
