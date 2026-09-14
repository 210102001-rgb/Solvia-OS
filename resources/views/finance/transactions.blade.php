@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Buku Besar Transaksi (Master Ledger)
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full uppercase">Single Source of Truth</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Audit log seluruh mutasi kas, revenue, expense, transfer, dan modal.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('finance.reports.export', ['type' => 'transactions']) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-1.5 border border-slate-700">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <button @click="createModal = true" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white transition flex items-center gap-1.5 shadow-lg shadow-indigo-600/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Transaksi Buku Besar
            </button>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('finance.transactions') }}" method="GET" class="w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tipe Mutasi</label>
                <select name="type" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Tipe</option>
                    <option value="INCOME" {{ request('type') == 'INCOME' ? 'selected' : '' }}>INCOME (Pemasukan)</option>
                    <option value="EXPENSE" {{ request('type') == 'EXPENSE' ? 'selected' : '' }}>EXPENSE (Pengeluaran)</option>
                    <option value="TRANSFER" {{ request('type') == 'TRANSFER' ? 'selected' : '' }}>TRANSFER (Antar Rekening)</option>
                    <option value="CAPITAL_IN" {{ request('type') == 'CAPITAL_IN' ? 'selected' : '' }}>CAPITAL_IN (Suntikan Modal)</option>
                    <option value="CAPITAL_OUT" {{ request('type') == 'CAPITAL_OUT' ? 'selected' : '' }}>CAPITAL_OUT (Prive / Penarikan)</option>
                    <option value="ADJUSTMENT" {{ request('type') == 'ADJUSTMENT' ? 'selected' : '' }}>ADJUSTMENT (Penyesuaian)</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Rekening Kas</label>
                <select name="account_id" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Rekening</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                            {{ $acc->account_name }} ({{ $acc->provider ?? $acc->bank_name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode, kategori, keterangan..." class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl px-3 py-2 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs py-2 px-3 rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('finance.transactions') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- TRANSACTIONS TABLE -->
    <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto touch-scroll">
            <table class="w-full min-w-[700px] text-left text-xs text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Kode Transaksi</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Tipe</th>
                        <th class="py-3.5 px-4">Kategori & Keterangan</th>
                        <th class="py-3.5 px-4">Rekening</th>
                        <th class="py-3.5 px-4">Project</th>
                        <th class="py-3.5 px-4 text-right">Nominal</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    @forelse($transactions as $tx)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-mono font-bold text-slate-200">
                            {{ $tx->transaction_code }}
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-400 whitespace-nowrap">
                            {{ $tx->transaction_date ? $tx->transaction_date->format('d M Y') : '-' }}
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $typeStyle = match($tx->transaction_type) {
                                    'INCOME' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                                    'EXPENSE' => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
                                    'TRANSFER' => 'bg-sky-500/20 text-sky-400 border-sky-500/30',
                                    'CAPITAL_IN' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                                    'CAPITAL_OUT' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                                    'ADJUSTMENT' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                                    default => 'bg-slate-700 text-slate-300 border-slate-600'
                                };
                            @endphp
                            <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full border {{ $typeStyle }}">
                                {{ $tx->transaction_type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 max-w-xs">
                            <span class="font-bold text-slate-100 block">{{ $tx->category }}</span>
                            <span class="text-[11px] text-slate-400 truncate block">{{ $tx->description }}</span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="font-bold text-slate-300">{{ $tx->account->account_name ?? '—' }}</span>
                            @if($tx->transaction_type === 'TRANSFER' && $tx->toAccount)
                                <span class="text-[10px] text-sky-400 block font-mono">➔ {{ $tx->toAccount->account_name }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($tx->project)
                                <span class="px-2 py-0.5 text-[10px] rounded bg-slate-800 text-indigo-300 border border-slate-700 font-mono">
                                    {{ Str::limit($tx->project->title, 18) }}
                                </span>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold whitespace-nowrap">
                            @if(in_array($tx->transaction_type, ['INCOME', 'CAPITAL_IN']))
                                <span class="text-emerald-400">+ Rp {{ number_format($tx->amount, 0, ',', '.') }}</span>
                            @elseif(in_array($tx->transaction_type, ['EXPENSE', 'CAPITAL_OUT']))
                                <span class="text-rose-400">- Rp {{ number_format($tx->amount, 0, ',', '.') }}</span>
                            @elseif($tx->transaction_type === 'TRANSFER')
                                <span class="text-sky-400">⇄ Rp {{ number_format($tx->amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-yellow-400">Rp {{ number_format($tx->amount, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 text-[9px] uppercase font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                                {{ $tx->status ?? 'posted' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-500">
                            Tidak ada data transaksi buku besar yang sesuai kriteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL CREATE MANUAL TRANSACTION -->
    <div x-show="createModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-cloak>
        <div @click.away="createModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 shadow-2xl relative text-left">
            <h3 class="text-lg font-black text-white mb-1">Catat Transaksi Buku Besar</h3>
            <p class="text-xs text-slate-400 mb-5">Pencatatan langsung ke master ledger dengan validasi prinsip akuntansi Solvia.Nova OS.</p>

            <form action="{{ route('finance.transactions.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tipe Transaksi *</label>
                        <select name="transaction_type" required class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-indigo-500">
                            <option value="INCOME">INCOME (Pemasukan)</option>
                            <option value="EXPENSE">EXPENSE (Pengeluaran)</option>
                            <option value="CAPITAL_IN">CAPITAL_IN (Suntikan Modal)</option>
                            <option value="CAPITAL_OUT">CAPITAL_OUT (Prive / Penarikan Modal)</option>
                            <option value="ADJUSTMENT">ADJUSTMENT (Koreksi Audit)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal *</label>
                        <input type="date" name="transaction_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Rekening Kas / Bank *</label>
                        <select name="financial_account_id" required class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-indigo-500">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->account_name }} (Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nominal (Rp) *</label>
                        <input type="number" step="0.01" min="1" name="amount" required placeholder="0" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 font-mono focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori *</label>
                        <input type="text" name="category" required placeholder="Project Revenue, Operational, etc." class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Project Terkait (Opsional)</label>
                        <select name="project_id" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-indigo-500">
                            <option value="">-- Tidak Terikat Project --</option>
                            @foreach($projects as $proj)
                                <option value="{{ $proj->id }}">{{ $proj->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Keterangan / Audit Memo *</label>
                    <textarea name="description" rows="2" required placeholder="Rincian alasan atau sumber transaksi..." class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs rounded-xl p-2.5 focus:ring-1 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="createModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl transition shadow-lg shadow-indigo-600/20">
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
