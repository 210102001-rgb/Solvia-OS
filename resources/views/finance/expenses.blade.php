@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ expenseModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Expense Ledger
                <span class="text-xs bg-rose-500/20 text-rose-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $expenses->total() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">All outbound costs — salary, infrastructure, software, operations, and project expenses</p>
        </div>
        <button @click="expenseModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-xs font-semibold text-white shadow-md shadow-rose-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Record Expense
        </button>
    </div>

    <!-- KPI STRIP -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Total Expense</span>
            <p class="text-xl font-black text-rose-400 font-mono mt-1">Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">This Month</span>
            @php $thisMonth = $expenses->getCollection()->where('date', '>=', now()->startOfMonth())->sum('amount'); @endphp
            <p class="text-xl font-black text-white font-mono mt-1">Rp {{ number_format($thisMonth, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Entries</span>
            <p class="text-xl font-black text-white font-mono mt-1">{{ $expenses->total() }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Categories</span>
            <p class="text-xl font-black text-slate-300 font-mono mt-1">{{ $expenses->getCollection()->pluck('category')->unique()->count() }}</p>
        </div>
    </div>

    <!-- EXPENSE TABLE — Adaptive (BK) -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-800 text-xs">
                <thead class="bg-slate-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Expense #</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Category</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Project / Vendor</th>
                        <th class="px-4 py-3 text-left font-bold text-slate-400 uppercase tracking-wider">Account</th>
                        <th class="px-4 py-3 text-right font-bold text-slate-400 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($expenses as $expense)
                    @php
                        $catColor = match($expense->category) {
                            'salary' => 'bg-indigo-500/20 text-indigo-300',
                            'infrastructure','hosting','domain' => 'bg-cyan-500/20 text-cyan-300',
                            'software' => 'bg-purple-500/20 text-purple-300',
                            'marketing' => 'bg-amber-500/20 text-amber-300',
                            'equipment' => 'bg-orange-500/20 text-orange-300',
                            default => 'bg-slate-800 text-slate-300',
                        };
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-5 py-3.5 font-mono font-bold text-rose-400">{{ $expense->expense_number }}</td>
                        <td class="px-4 py-3.5 text-slate-300 font-mono">{{ $expense->date->format('d M Y') }}</td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $catColor }}">{{ $expense->category }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-slate-300">
                            {{ $expense->project ? $expense->project->name : '—' }}
                            @if($expense->vendor)<span class="block text-[10px] text-slate-500">{{ $expense->vendor }}</span>@endif
                            @if($expense->notes)<span class="block text-[10px] text-slate-500 truncate max-w-xs">{{ $expense->notes }}</span>@endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-400">{{ $expense->account ? $expense->account->account_name : '—' }}</td>
                        <td class="px-4 py-3.5 text-right">
                            <span class="font-mono font-black text-rose-400 text-sm">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">No expense entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-800 px-5 py-3">
            {{ $expenses->links() }}
        </div>
    </div>

    <!-- MODAL: RECORD EXPENSE -->
    <div x-cloak x-show="expenseModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="expenseModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="expenseModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="expenseModal = false">
            <div x-show="expenseModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Record Expense Entry</h3>
                    <button @click="expenseModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('finance.expenses.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Expense Number *</label>
                            <input type="text" name="expense_number" value="EXP-{{ date('Y') }}-{{ str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Date *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Category *</label>
                            <select name="category" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                                <option value="salary">Salary</option>
                                <option value="infrastructure">Infrastructure</option>
                                <option value="software">Software</option>
                                <option value="hosting">Hosting</option>
                                <option value="domain">Domain</option>
                                <option value="marketing">Marketing</option>
                                <option value="equipment">Equipment</option>
                                <option value="project">Project</option>
                                <option value="transportation">Transportation</option>
                                <option value="operational">Operational</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Amount (Rp) *</label>
                            <input type="number" name="amount" min="1" required placeholder="e.g. 5000000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-rose-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Account (Debit) *</label>
                            <select name="account_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Project</label>
                            <select name="project_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                                <option value="">Not project-specific</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Vendor / Payee</label>
                        <input type="text" name="vendor" placeholder="e.g. DigitalOcean, Tokopedia, Supplier XYZ" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Notes</label>
                        <textarea name="notes" rows="2" placeholder="Description or context for this expense" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-rose-500"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="expenseModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-600/20">Record Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
