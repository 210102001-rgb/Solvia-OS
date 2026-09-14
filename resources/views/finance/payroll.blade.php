@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ payrollModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Payroll Engine
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Restricted</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Super Admin only — salary calculation, approval, and disbursement</p>
        </div>
        <button @click="payrollModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Payroll Entry
        </button>
    </div>

    <!-- PAYROLL RECORDS — Mobile-Adaptive Cards (Section BK) -->
    <div class="space-y-3">
        @forelse($payrolls as $payroll)
        @php
            $statusColor = match($payroll->payment_status) {
                'paid' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                'approved' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
                'reviewed' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                default => 'bg-slate-800 text-slate-300 border-slate-700',
            };
        @endphp
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800 text-xs shadow-md">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <!-- Left: Employee Info -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/30 flex items-center justify-center font-black text-indigo-300 text-sm flex-shrink-0">
                        {{ strtoupper(substr($payroll->user->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-bold text-white text-sm">{{ $payroll->user->name }}</p>
                        <p class="text-slate-400 text-[10px] mt-0.5 capitalize">{{ str_replace('_', ' ', $payroll->user->role) }} • Period: <span class="font-mono">{{ $payroll->period }}</span></p>
                    </div>
                </div>

                <!-- Middle: Salary Breakdown -->
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-3 text-center flex-1 sm:flex-none">
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Base</p>
                        <p class="font-mono text-slate-200 font-semibold">{{ number_format($payroll->base_salary / 1000000, 1) }}M</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Allow</p>
                        <p class="font-mono text-emerald-400">+{{ number_format($payroll->allowance / 1000000, 1) }}M</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Bonus</p>
                        <p class="font-mono text-emerald-400">+{{ number_format($payroll->bonus / 1000000, 1) }}M</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Deduct</p>
                        <p class="font-mono text-rose-400">-{{ number_format($payroll->deduction / 1000000, 1) }}M</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-slate-400 uppercase font-bold">Total</p>
                        <p class="font-mono font-black text-white">Rp {{ number_format($payroll->total / 1000000, 1) }}M</p>
                    </div>
                </div>

                <!-- Right: Status + Action -->
                <div class="flex items-center gap-3 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-800">
                    <span class="px-2 py-1 text-[9px] font-bold uppercase rounded border {{ $statusColor }}">{{ $payroll->payment_status }}</span>

                    @if($payroll->payment_status !== 'paid')
                    @php
                        $confirmMsg = 'Konfirmasi pencairan gaji untuk ' . ($payroll->user ? $payroll->user->name : 'karyawan') . '? Sebesar Rp ' . number_format($payroll->total, 0, ',', '.') . ' akan dicatat sebagai beban pengeluaran (mengurangi profit).';
                    @endphp
                    <form action="{{ route('finance.payroll.pay', $payroll->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('{{ addslashes($confirmMsg) }}')"
                            class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-lg shadow-indigo-600/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Disburse
                        </button>
                    </form>
                    @else
                    <div class="text-right">
                        <span class="text-[10px] text-emerald-400 font-semibold flex items-center gap-1 justify-end">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Telah Dicairkan
                        </span>
                        <span class="text-[9px] text-slate-500 block">
                            {{ $payroll->paid_at ? $payroll->paid_at->format('d M Y H:i') : 'Paid' }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            No payroll records found. Generate the first payroll entry above.
        </div>
        @endforelse

        <div class="pt-2">{{ $payrolls->links() }}</div>
    </div>

    <!-- MODAL: NEW PAYROLL ENTRY -->
    <div x-cloak x-show="payrollModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="payrollModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="payrollModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="payrollModal = false">
            <div x-show="payrollModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto" x-data="{
                base: 0, allowance: 0, bonus: 0, deduction: 0, reimbursement: 0,
                get total() { return (Number(this.base) + Number(this.allowance) + Number(this.bonus) + Number(this.reimbursement)) - Number(this.deduction); }
            }">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Generate Payroll Entry</h3>
                    <button @click="payrollModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('finance.payroll.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Employee *</label>
                            <select name="user_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Period (YYYY-MM) *</label>
                            <input type="text" name="period" value="{{ date('Y-m') }}" pattern="\d{4}-\d{2}" required placeholder="e.g. 2026-09" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Base Salary (Rp) *</label>
                            <input type="number" name="base_salary" x-model="base" min="0" required placeholder="e.g. 8000000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Allowance (Rp)</label>
                            <input type="number" name="allowance" x-model="allowance" min="0" placeholder="Transport, meal, etc." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Bonus (Rp)</label>
                            <input type="number" name="bonus" x-model="bonus" min="0" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Deduction (Rp)</label>
                            <input type="number" name="deduction" x-model="deduction" min="0" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Reimbursement (Rp)</label>
                            <input type="number" name="reimbursement" x-model="reimbursement" min="0" placeholder="0" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Live Total Preview -->
                    <div class="p-3 rounded-xl bg-indigo-950/50 border border-indigo-500/20 flex items-center justify-between">
                        <span class="text-slate-400 font-semibold text-xs">Calculated Total</span>
                        <span class="font-mono font-black text-white text-base" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(total)"></span>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Source Account (Rekening Pembayaran)</label>
                        <select name="account_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name ?: 'Kas Utama' }} (Saldo: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <label class="flex items-start gap-2.5 cursor-pointer p-3 rounded-xl bg-slate-950/60 border border-slate-800 hover:border-indigo-500/50 transition">
                        <input type="checkbox" name="disburse_now" value="1" checked class="mt-0.5 w-4 h-4 rounded text-indigo-600 bg-slate-900 border-slate-700 focus:ring-indigo-500">
                        <div class="text-xs">
                            <span class="font-semibold text-white">Cairkan & Catat Pengeluaran Langsung</span>
                            <p class="text-[10px] text-slate-400 mt-0.5">Otomatis mencatat beban pengeluaran (Expense gaji), mengurangi laba/profit perusahaan, dan memotong saldo rekening.</p>
                        </div>
                    </label>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="payrollModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/20">Generate & Process Payroll</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
