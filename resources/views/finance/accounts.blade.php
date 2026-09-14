@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    addModal: false,
    transferModal: false,
    adjustModal: false,
    selectedAccount: null
}">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Corporate Financial Accounts
                <span class="text-xs bg-sky-500/20 text-sky-300 font-bold px-2 py-0.5 rounded-full uppercase">Treasury</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Rekening Bank, E-Wallet, Kas & Gateway • Saldo Terkalkulasi Otomatis dari Buku Besar</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="transferModal = true" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Transfer Antar Rekening
            </button>
            <button @click="adjustModal = true" class="px-3 py-1.5 rounded-xl bg-amber-600/30 hover:bg-amber-600/40 text-amber-300 border border-amber-500/30 text-xs font-semibold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                Audit Adjustment
            </button>
            <button @click="addModal = true" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white transition flex items-center gap-1.5 shadow-lg shadow-indigo-600/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Tambah Rekening
            </button>
        </div>
    </div>

    <!-- SUMMARY BANNER -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950/40 to-slate-900 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Total Kas & Likuiditas Tersedia</span>
            <p class="text-2xl sm:text-3xl font-black text-white font-mono mt-0.5">Rp {{ number_format($totalCash, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Saldo aktual dihitung dari seluruh transaksi buku besar tanpa manipulasi langsung.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3 py-2 rounded-xl bg-slate-950/70 border border-slate-800 text-center">
                <span class="text-[10px] text-slate-400 block font-semibold">Total Rekening</span>
                <span class="text-base font-mono font-bold text-white">{{ $accounts->count() }}</span>
            </div>
            <div class="px-3 py-2 rounded-xl bg-slate-950/70 border border-slate-800 text-center">
                <span class="text-[10px] text-slate-400 block font-semibold">Status</span>
                <span class="text-base font-mono font-bold text-emerald-400">Reconciled</span>
            </div>
        </div>
    </div>

    <!-- ACCOUNTS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($accounts as $acc)
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between space-y-4 hover:border-slate-700 transition">
            <div>
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded bg-indigo-500/20 text-indigo-300 font-mono">
                        {{ $acc->account_code }}
                    </span>
                    @php
                        $typeBadge = match($acc->account_type) {
                            'BANK' => 'bg-sky-500/20 text-sky-300',
                            'E_WALLET' => 'bg-emerald-500/20 text-emerald-300',
                            'CASH' => 'bg-amber-500/20 text-amber-300',
                            'PAYMENT_GATEWAY' => 'bg-purple-500/20 text-purple-300',
                            default => 'bg-slate-700 text-slate-300'
                        };
                    @endphp
                    <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded {{ $typeBadge }}">
                        {{ $acc->account_type ?? 'BANK' }}
                    </span>
                </div>

                <h3 class="font-bold text-white text-base mt-2">{{ $acc->account_name }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ $acc->provider ?? $acc->bank_name }} • <span class="font-mono text-slate-300">{{ $acc->account_number ?? '-' }}</span></p>
                <p class="text-[11px] text-slate-500 mt-0.5">Atas Nama: <span class="text-slate-300">{{ $acc->owner ?? 'Solvia Nova' }}</span></p>

                <div class="mt-4 p-3 rounded-xl bg-slate-950 border border-slate-800/80">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400">Saldo Saldo Buku:</span>
                        <span class="font-mono font-bold text-white text-base">Rp {{ number_format($acc->balance, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-500 mt-1">
                        <span>Saldo Awal: Rp {{ number_format($acc->opening_balance, 0, ',', '.') }}</span>
                        <span>{{ $acc->transactions_count }} Transaksi</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                <span class="text-[10px] {{ $acc->is_active ? 'text-emerald-400' : 'text-slate-500' }} flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $acc->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                    {{ $acc->is_active ? 'Active' : 'Archived' }}
                </span>
                <a href="{{ route('finance.transactions', ['account_id' => $acc->id]) }}" class="text-[11px] text-indigo-400 hover:text-indigo-300 font-semibold">
                    Buku Besar &rarr;
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-3 p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            Belum ada rekening keuangan terdaftar. Daftarkan rekening pertama di atas.
        </div>
        @endforelse
    </div>

    <!-- MODAL: ADD ACCOUNT -->
    <div x-cloak x-show="addModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="addModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="addModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="addModal = false">
            <div x-show="addModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Tambah Rekening Keuangan Baru</h3>
                    <button @click="addModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('finance.accounts.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Kode Rekening *</label>
                            <input type="text" name="account_code" required placeholder="e.g. ACC-BCA-01" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Tipe Akun *</label>
                            <select name="account_type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="BANK">BANK</option>
                                <option value="E_WALLET">E-WALLET</option>
                                <option value="CASH">CASH / KAS FISIK</option>
                                <option value="PAYMENT_GATEWAY">PAYMENT GATEWAY</option>
                                <option value="OTHER">LAINNYA</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Nama Rekening / Akun *</label>
                            <input type="text" name="account_name" required placeholder="e.g. BCA Operasional Utama" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Provider / Bank</label>
                            <input type="text" name="provider" placeholder="e.g. Bank Central Asia" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Nomor Rekening / Identifikasi</label>
                            <input type="text" name="account_number" placeholder="e.g. 1234567890" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Atas Nama (Owner)</label>
                            <input type="text" name="owner" placeholder="e.g. PT Solvia Nova Indonesia" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Saldo Awal (Opening Balance) *</label>
                            <input type="number" name="opening_balance" min="0" value="0" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                            <span class="text-[9px] text-slate-500 mt-0.5 block">Otomatis dicatat sebagai OPENING_BALANCE di ledger.</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Mata Uang</label>
                            <input type="text" name="currency" value="IDR" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Keterangan / Catatan</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/20">Simpan Rekening</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: TRANSFER ANTAR REKENING -->
    <div x-cloak x-show="transferModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="transferModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="transferModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="transferModal = false">
            <div x-show="transferModal" x-transition @click.stop class="relative z-20 w-full max-w-md rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Transfer Antar Rekening Perusahaan</h3>
                    <button @click="transferModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('finance.accounts.transfer') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Rekening Sumber (Pengirim) *</label>
                        <select name="from_account_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (Saldo: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Rekening Tujuan (Penerima) *</label>
                        <select name="to_account_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (Saldo: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Nominal Transfer (Rp) *</label>
                            <input type="number" name="amount" min="1" required placeholder="e.g. 5000000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Tanggal Transfer *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Catatan / Alasan Transfer</label>
                        <input type="text" name="notes" placeholder="e.g. Topup dana operasional harian" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <div class="p-3 rounded-xl bg-slate-950/70 border border-slate-800 text-[10px] text-slate-400">
                        Prinsip Finansial: Transfer antar rekening tidak mengubah total kas perusahaan dan <span class="text-white font-semibold">TIDAK dihitung sebagai beban pengeluaran (Expense)</span> ataupun pendapatan.
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="transferModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-lg shadow-sky-600/20">Eksekusi Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: AUDIT ADJUSTMENT -->
    <div x-cloak x-show="adjustModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="adjustModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="adjustModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="adjustModal = false">
            <div x-show="adjustModal" x-transition @click.stop class="relative z-20 w-full max-w-md rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Penyesuaian Saldo (Audit Adjustment)</h3>
                    <button @click="adjustModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('finance.accounts.adjust') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Rekening yang Disesuaikan *</label>
                        <select name="account_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->account_name }} (Saldo Saat Ini: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Nominal Penyesuaian (Rp) *</label>
                            <input type="number" step="any" name="amount" required placeholder="Gunakan minus jika berkurang" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                            <span class="text-[9px] text-slate-500 block mt-0.5">Contoh: +50000 atau -50000</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Tanggal *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Alasan Penyesuaian (Wajib untuk Audit Trail) *</label>
                        <textarea name="reason" rows="2" required placeholder="Misal: Selisih biaya admin bank / bunga tabungan yang belum tercatat" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="adjustModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/20">Catat Penyesuaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
