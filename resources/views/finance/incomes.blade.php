@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ incomeModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Income Ledger
                <span class="text-xs bg-emerald-500/20 text-emerald-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $incomes->total() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">All inbound revenue streams — client payments, project income, and other sources</p>
        </div>
        <button @click="incomeModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white shadow-md shadow-emerald-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Record Income
        </button>
    </div>

    <!-- KPI STRIP -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Income</span>
            <p class="text-xl font-black text-emerald-400 font-mono mt-1">Rp {{ number_format($incomes->sum('amount'), 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">This Month</span>
            @php $thisMonth = $incomes->getCollection()->where('date', '>=', now()->startOfMonth())->sum('amount'); @endphp
            <p class="text-xl font-black text-white font-mono mt-1">Rp {{ number_format($thisMonth, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Entries</span>
            <p class="text-xl font-black text-white font-mono mt-1">{{ $incomes->total() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Avg. Entry</span>
            @php $avg = $incomes->total() > 0 ? $incomes->sum('amount') / $incomes->total() : 0; @endphp
            <p class="text-xl font-black text-slate-300 font-mono mt-1">Rp {{ number_format($avg, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- INCOME TABLE — Adaptive (BK) -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-xs">
                <thead class="bg-slate-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Income #</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Source / Category</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Client / Project</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Account</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-400 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($incomes as $income)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-5 py-3.5 font-mono font-bold text-emerald-400">{{ $income->income_number }}</td>
                        <td class="px-4 py-3.5 text-slate-300 font-mono">{{ $income->date->format('d M Y') }}</td>
                        <td class="px-4 py-3.5">
                            <p class="font-semibold text-white">{{ $income->source }}</p>
                            <p class="text-slate-500 text-[10px] mt-0.5 capitalize">{{ $income->category }}</p>
                        </td>
                        <td class="px-4 py-3.5 text-slate-300">
                            {{ $income->client ? $income->client->name : '—' }}
                            @if($income->project)<span class="block text-[10px] text-indigo-400">{{ $income->project->name }}</span>@endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-400">{{ $income->account ? $income->account->account_name : '—' }}</td>
                        <td class="px-4 py-3.5 text-right">
                            <span class="font-mono font-black text-emerald-400 text-sm">Rp {{ number_format($income->amount, 0, ',', '.') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">No income entries found. Record your first income above.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-800 px-5 py-3">
            {{ $incomes->links() }}
        </div>
    </div>

    <!-- MODAL: RECORD INCOME -->
    <div x-cloak x-show="incomeModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="incomeModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="incomeModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="incomeModal = false">
            <div x-show="incomeModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Record Income Entry</h3>
                    <button @click="incomeModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('finance.incomes.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Income Number *</label>
                            <input type="text" name="income_number" value="INC-{{ date('Y') }}-{{ str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Date *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Source Description *</label>
                        <input type="text" name="source" required placeholder="e.g. Project milestone payment — Phase 1" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Client</label>
                            <select name="client_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                                <option value="">No specific client</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Project</label>
                            <select name="project_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                                <option value="">No specific project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Category *</label>
                            <select name="category" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                                <option value="Client Invoice Payment">Client Invoice Payment</option>
                                <option value="Project Milestone">Project Milestone</option>
                                <option value="Consultation">Consultation</option>
                                <option value="Retainer">Retainer</option>
                                <option value="Royalty">Royalty</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Amount (Rp) *</label>
                            <input type="number" name="amount" min="1" required placeholder="e.g. 25000000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-emerald-500">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Destination Account *</label>
                        <select name="account_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} (Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Notes</label>
                        <textarea name="notes" rows="2" placeholder="Additional context or remarks" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-emerald-500"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="incomeModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/20">Record Income</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
