@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div class="flex items-center gap-3">
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Notifications</h1>
            @php $unread = $notifications->where('is_read', false)->count(); @endphp
            @if($unread > 0)
            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-indigo-500/20 text-indigo-300">{{ $unread }} unread</span>
            @endif
        </div>
        <form action="{{ route('communication.notifications.readAll') }}" method="POST">
            @csrf
            <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs font-semibold text-slate-200 transition">
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Mark All Read
            </button>
        </form>
    </div>

    <!-- NOTIFICATION LIST -->
    <div class="space-y-2">
        @forelse($notifications as $notif)
        @php
            $levelColor = match($notif->level ?? 'info') {
                'danger'  => 'border-rose-500/30 bg-rose-950/10',
                'warning' => 'border-amber-500/30 bg-amber-950/10',
                'success' => 'border-emerald-500/30 bg-emerald-950/10',
                default   => 'border-slate-800',
            };
            $iconColor = match($notif->level ?? 'info') {
                'danger'  => 'text-rose-400 bg-rose-500/10',
                'warning' => 'text-amber-400 bg-amber-500/10',
                'success' => 'text-emerald-400 bg-emerald-500/10',
                default   => 'text-indigo-400 bg-indigo-500/10',
            };
        @endphp
        <div class="p-4 rounded-2xl bg-slate-900/90 border {{ $levelColor }} {{ !$notif->is_read ? 'border-l-2' : '' }} transition">
            <div class="flex items-start gap-3">
                <!-- Icon -->
                <div class="flex-shrink-0 mt-0.5 w-9 h-9 rounded-xl {{ $iconColor }} flex items-center justify-center">
                    @if(str_contains(strtolower($notif->type ?? ''), 'task'))
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    @elseif(str_contains(strtolower($notif->type ?? ''), 'invoice') || str_contains(strtolower($notif->type ?? ''), 'finance'))
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    @elseif(str_contains(strtolower($notif->type ?? ''), 'blocker'))
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @elseif(str_contains(strtolower($notif->type ?? ''), 'infra') || str_contains(strtolower($notif->type ?? ''), 'domain') || str_contains(strtolower($notif->type ?? ''), 'expir'))
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif(str_contains(strtolower($notif->type ?? ''), 'purchase'))
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-bold {{ $notif->is_read ? 'text-slate-300' : 'text-white' }}">
                            {{ $notif->title }}
                        </p>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if(!$notif->is_read)
                                <span class="w-2 h-2 rounded-full bg-indigo-400 flex-shrink-0"></span>
                            @endif
                            <span class="text-[11px] text-slate-500 whitespace-nowrap">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">{{ $notif->message }}</p>
                    <div class="mt-2 flex items-center gap-3">
                        @if($notif->action_url)
                        <a href="{{ $notif->action_url }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition">View Details &rarr;</a>
                        @endif
                        @if(!$notif->is_read)
                        <form action="{{ route('communication.notifications.read', $notif->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-slate-500 hover:text-slate-300 transition">Mark as read</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center rounded-2xl bg-slate-900 border border-slate-800">
            <svg class="mx-auto w-12 h-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-sm font-medium text-white">All caught up!</p>
            <p class="text-xs text-slate-500 mt-1">No notifications at the moment.</p>
        </div>
        @endforelse
    </div>

    <div>{{ $notifications->links() }}</div>
</div>
@endsection
