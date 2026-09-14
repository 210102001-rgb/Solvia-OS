@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ reimbModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Reimbursements
                <span class="text-xs bg-amber-500/20 text-amber-300 font-bold px-2 py-0.5 rounded-full font-mono">{{ $reimbursements->total() }}</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                @if(Auth::user()->isSuperAdmin())
                    All reimbursement claims — review, approve, and disburse
                @else
                    Your reimbursement claims — submit and track approval status
                @endif
            </p>
        </div>
        <button @click="reimbModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-xs font-semibold text-white shadow-md shadow-amber-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Submit Claim
        </button>
    </div>

    <!-- REIMBURSEMENT LIST — Mobile Adaptive Cards (Section BK) -->
    <div class="space-y-3">
        @forelse($reimbursements as $reimb)
        @php
            $statusColor = match($reimb->status) {
                'approved' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                'paid' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
                'rejected' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                'in_review' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                default => 'bg-slate-800 text-slate-300 border-slate-700',
            };
        @endphp
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800 text-xs shadow-md">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-bold text-white text-sm">{{ $reimb->title }}</p>
                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded border {{ $statusColor }}">{{ str_replace('_', ' ', $reimb->status) }}</span>
                    </div>
                    <p class="text-slate-400 mt-1">
                        By: <span class="text-slate-200 font-semibold">{{ $reimb->user->name }}</span>
                        • {{ $reimb->date->format('d M Y') }}
                        @if($reimb->project) • <span class="text-indigo-400">{{ $reimb->project->name }}</span> @endif
                    </p>
                    <p class="text-slate-500 text-[10px] mt-0.5 capitalize">Category: {{ $reimb->category }}</p>
                    @if($reimb->notes)<p class="text-slate-500 text-[10px] mt-0.5 truncate">{{ $reimb->notes }}</p>@endif
                </div>
                <div class="flex items-center gap-4 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-800">
                    <div class="text-right">
                        <span class="text-[9px] text-slate-400 uppercase font-bold block">Amount</span>
                        <span class="font-mono font-black text-white text-base">Rp {{ number_format($reimb->amount, 0, ',', '.') }}</span>
                    </div>
                    @if(Auth::user()->isSuperAdmin() && in_array($reimb->status, ['submitted', 'in_review']))
                    <div class="flex gap-2">
                        <form action="{{ route('finance.reimbursements.approve', $reimb->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition">Approve</button>
                        </form>
                        <form action="{{ route('finance.reimbursements.approve', $reimb->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-700 hover:bg-rose-600 text-white font-bold text-xs transition">Reject</button>
                        </form>
                    </div>
                    @elseif($reimb->approver)
                        <span class="text-[10px] text-slate-500">by {{ $reimb->approver->name }}</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center rounded-2xl bg-slate-900 border border-slate-800 text-slate-500 text-xs">
            No reimbursement claims found.
        </div>
        @endforelse

        <div class="pt-2">{{ $reimbursements->links() }}</div>
    </div>

    <!-- MODAL: SUBMIT REIMBURSEMENT CLAIM -->
    <div x-cloak x-show="reimbModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="reimbModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="reimbModal = false" aria-hidden="true"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="reimbModal = false">
            <div x-show="reimbModal" x-transition @click.stop class="relative z-20 w-full max-w-lg rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Submit Reimbursement Claim</h3>
                    <button @click="reimbModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('finance.reimbursements.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Claim Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Transportation for client meeting" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Amount (Rp) *</label>
                            <input type="number" name="amount" min="1" required placeholder="e.g. 250000" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Date *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Category *</label>
                            <select name="category" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                                <option value="transportation">Transportation</option>
                                <option value="accommodation">Accommodation</option>
                                <option value="meals">Meals</option>
                                <option value="equipment">Equipment</option>
                                <option value="software">Software</option>
                                <option value="operational">Operational</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Related Project</label>
                            <select name="project_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500">
                                <option value="">Not project-specific</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Notes / Justification</label>
                        <textarea name="notes" rows="2" placeholder="Describe the expense and its business purpose" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-amber-500"></textarea>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="reimbModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/20">Submit for Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
