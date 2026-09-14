@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-0 sm:px-2 py-2 sm:py-4" x-data="{
    view: 'calendar',
    dateModal: false,
    selectedDate: '',
    selectedDateTitle: '',
    selectedEvents: [],
    openDateModal(date, title, events) {
        this.selectedDate = date;
        this.selectedDateTitle = title;
        this.selectedEvents = events || [];
        this.dateModal = true;
    }
}">

    <!-- Flash Alert with smooth animation -->
    @if(session('success'))
    <div x-data="{ showNotice: true }" x-show="showNotice"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
         class="mb-6 p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 flex items-center justify-between shadow-xl shadow-emerald-950/50">
        <div class="flex items-center gap-3">
            <span class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </span>
            <div>
                <p class="text-xs font-bold text-white">{{ session('success') }}</p>
                <p class="text-[11px] text-emerald-400/80">Pengingat jadwal & deadline telah diperbarui secara otomatis.</p>
            </div>
        </div>
        <button type="button" @click="showNotice = false" class="text-emerald-400 hover:text-white text-lg font-bold p-1 leading-none">&times;</button>
    </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Schedule & Calendar</h1>
            <p class="mt-1 text-xs text-slate-400">Klik tanggal pada kalender untuk melihat rincian agenda, deadline tugas, dan progres operasional.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <form action="{{ route('schedule.triggerReminders') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-slate-700/80 bg-slate-800 hover:bg-slate-700 px-3 py-1.5 text-xs font-semibold text-white shadow-md transition-all duration-150 active:scale-95">
                    <svg class="-ml-0.5 mr-1.5 h-3.5 w-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    Trigger Reminders
                </button>
            </form>
            
            <div class="flex rounded-xl bg-slate-900 border border-slate-800 p-1 shadow-inner">
                <button @click="view = 'calendar'" :class="view === 'calendar' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-150">
                    Calendar
                </button>
                <button @click="view = 'list'" :class="view === 'list' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white'" class="px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-150">
                    List
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-slate-900/80 p-3.5 sm:p-4 rounded-2xl border border-slate-800 mb-6 backdrop-blur-sm">
        <form method="GET" action="{{ route('schedule.index') }}" class="flex flex-wrap gap-3 items-center">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Filter:</span>
            <label class="inline-flex items-center text-xs text-slate-300 cursor-pointer">
                <input type="checkbox" name="types[]" value="task" class="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 mr-1.5">
                Tasks
            </label>
            <label class="inline-flex items-center text-xs text-slate-300 cursor-pointer">
                <input type="checkbox" name="types[]" value="milestone" class="rounded border-slate-700 bg-slate-950 text-purple-600 focus:ring-purple-500 mr-1.5">
                Milestones
            </label>
            <label class="inline-flex items-center text-xs text-slate-300 cursor-pointer">
                <input type="checkbox" name="types[]" value="progress" class="rounded border-slate-700 bg-slate-950 text-emerald-600 focus:ring-emerald-500 mr-1.5">
                Daily Progress
            </label>
            @if(Auth::user()->isSuperAdmin())
            <label class="inline-flex items-center text-xs text-slate-300 cursor-pointer">
                <input type="checkbox" name="types[]" value="invoice" class="rounded border-slate-700 bg-slate-950 text-amber-600 focus:ring-amber-500 mr-1.5">
                Invoices
            </label>
            @endif
            <button type="submit" class="w-full sm:w-auto sm:ml-auto inline-flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition">
                Apply Filters
            </button>
        </form>
    </div>

    <!-- Calendar View -->
    <div x-show="view === 'calendar'" class="bg-slate-900 rounded-3xl border border-slate-800 overflow-hidden shadow-2xl">
        <!-- Month Navigation Bar -->
        <div class="flex items-center justify-between px-4 sm:px-6 py-3.5 border-b border-slate-800 bg-slate-950/60">
            <div class="flex items-center gap-2.5">
                <h2 class="text-base sm:text-xl font-bold text-white tracking-tight">
                    {{ $currentDate->format('F Y') }}
                </h2>
                <a href="{{ route('schedule.index') }}" class="px-2 py-0.5 text-xs font-semibold rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition">
                    Today
                </a>
            </div>
            <div class="flex items-center gap-1.5">
                <a href="{{ route('schedule.index', ['month' => $prevMonthDate->month, 'year' => $prevMonthDate->year]) }}" class="p-1.5 sm:p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 hover:border-slate-700 transition" title="Previous Month">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </a>
                <a href="{{ route('schedule.index', ['month' => $nextMonthDate->month, 'year' => $nextMonthDate->year]) }}" class="p-1.5 sm:p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 hover:border-slate-700 transition" title="Next Month">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
        </div>

        <!-- Days of Week Header -->
        <div class="grid grid-cols-7 border-b border-slate-800 bg-slate-950/80">
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                <div class="py-2 sm:py-3 text-center text-[10px] sm:text-xs font-bold text-slate-400 tracking-wider uppercase">
                    <span class="hidden sm:inline">{{ $dayName }}</span>
                    <span class="sm:hidden">{{ substr($dayName, 0, 1) }}</span>
                </div>
            @endforeach
        </div>

        <!-- Dates Grid -->
        <div class="grid grid-cols-7 gap-px bg-slate-800">
            {{-- Blank cells before the 1st of the month --}}
            @for($b = 0; $b < $startDayOfWeek; $b++)
                <div class="bg-slate-950/40 min-h-[56px] sm:min-h-[115px] p-1.5 sm:p-2"></div>
            @endfor

            {{-- Days 1 to daysInMonth --}}
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $cellDate = $currentDate->copy()->day($day);
                    $isToday = $cellDate->isToday();
                    $dayEvents = $eventsByDay[$day] ?? collect();
                    $dayEventsArray = $dayEvents->values()->toArray();
                @endphp
                <div @click="openDateModal('{{ $cellDate->format('Y-m-d') }}', '{{ $cellDate->format('l, d F Y') }}', {{ json_encode($dayEventsArray) }})"
                     class="group cursor-pointer bg-slate-900 min-h-[56px] sm:min-h-[115px] p-1.5 sm:p-2.5 flex flex-col justify-between transition-all duration-200 hover:bg-slate-800/90 hover:ring-1 hover:ring-indigo-500/50 {{ $isToday ? 'bg-indigo-950/30 ring-1 ring-inset ring-indigo-500/70' : '' }}"
                     title="Klik untuk membuka agenda {{ $cellDate->format('d M Y') }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center justify-center text-[11px] sm:text-sm font-bold transition-all duration-200 group-hover:scale-110 {{ $isToday ? 'w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/40 text-[10px] sm:text-xs' : 'text-slate-300' }}">
                            {{ $day }}
                        </span>
                        @if($dayEvents->count() > 0)
                            <span class="text-[9px] sm:text-[10px] font-bold px-1 sm:px-1.5 py-0.2 sm:py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                {{ $dayEvents->count() }}
                            </span>
                        @else
                            <span class="hidden sm:inline opacity-0 group-hover:opacity-100 transition text-[10px] text-slate-500 font-semibold">
                                + info
                            </span>
                        @endif
                    </div>

                    {{-- Events preview for this date --}}
                    <!-- Mobile: Dot indicators -->
                    <div class="flex sm:hidden items-center justify-center gap-1 mt-auto pt-1">
                        @foreach($dayEvents->take(3) as $ev)
                            <span class="w-1.5 h-1.5 rounded-full {{ ($ev['badge_color'] ?? '') === 'emerald' ? 'bg-emerald-400' : (($ev['badge_color'] ?? '') === 'rose' ? 'bg-rose-400' : (($ev['badge_color'] ?? '') === 'purple' ? 'bg-purple-400' : 'bg-indigo-400')) }}"></span>
                        @endforeach
                    </div>

                    <!-- Desktop: Full badges -->
                    <div class="hidden sm:flex flex-1 flex-col gap-1 overflow-hidden">
                        @foreach($dayEvents->take(2) as $ev)
                            <div class="truncate text-[10px] px-2 py-1 rounded-md border font-medium transition-all {{ ($ev['badge_color'] ?? '') === 'emerald' ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : (($ev['badge_color'] ?? '') === 'rose' ? 'bg-rose-500/15 border-rose-500/30 text-rose-300' : (($ev['badge_color'] ?? '') === 'purple' ? 'bg-purple-500/15 border-purple-500/30 text-purple-300' : 'bg-indigo-500/15 border-indigo-500/30 text-indigo-300')) }}">
                                {{ $ev['title'] }}
                            </div>
                        @endforeach
                        @if($dayEvents->count() > 2)
                            <span class="text-[9px] text-slate-400 font-medium pl-1">
                                +{{ $dayEvents->count() - 2 }} lainnya...
                            </span>
                        @endif
                    </div>
                </div>
            @endfor

            {{-- Blank cells after the last day of month --}}
            @php
                $totalCells = $startDayOfWeek + $daysInMonth;
                $remainingCells = (7 - ($totalCells % 7)) % 7;
            @endphp
            @for($r = 0; $r < $remainingCells; $r++)
                <div class="bg-slate-950/40 min-h-[56px] sm:min-h-[115px] p-1.5 sm:p-2"></div>
            @endfor
        </div>
    </div>

    <!-- List View -->
    <div x-show="view === 'list'" style="display: none;" class="bg-slate-900 rounded-3xl border border-slate-800 overflow-hidden shadow-2xl">
        <ul role="list" class="divide-y divide-slate-800">
            @forelse($events ?? [] as $event)
            <li @click="openDateModal('{{ $event['date'] ?? '' }}', '{{ \Carbon\Carbon::parse($event['date'] ?? now())->format('l, d F Y') }}', [{{ json_encode($event) }}])"
                class="p-4 sm:px-6 hover:bg-slate-800/60 transition cursor-pointer group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            @if(($event['category'] ?? '') === 'Daily Progress')
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 group-hover:scale-105 transition">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </span>
                            @elseif(($event['category'] ?? '') === 'Task Deadline')
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-indigo-500/15 text-indigo-400 border border-indigo-500/30 group-hover:scale-105 transition">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                </span>
                            @elseif(($event['category'] ?? '') === 'Milestone Deadline')
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-purple-500/15 text-purple-400 border border-purple-500/30 group-hover:scale-105 transition">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                                </span>
                            @elseif(($event['category'] ?? '') === 'Invoice Due')
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-amber-500/15 text-amber-400 border border-amber-500/30 group-hover:scale-105 transition">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-indigo-500/15 text-indigo-400 border border-indigo-500/30 group-hover:scale-105 transition">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white group-hover:text-indigo-300 transition">{{ $event['title'] ?? 'Event' }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($event['date'] ?? now())->format('F j, Y') }} • 
                                <span class="text-indigo-400 font-medium">{{ $event['category'] ?? 'Schedule' }}</span>
                                @if(!empty($event['context']))
                                    • <span class="text-slate-400">{{ $event['context'] }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @php
                            $badge = $event['badge_color'] ?? 'indigo';
                            $badgeClass = match($badge) {
                                'emerald' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                                'rose' => 'bg-rose-500/15 text-rose-300 border-rose-500/30',
                                'amber', 'orange' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                                'purple' => 'bg-purple-500/15 text-purple-300 border-purple-500/30',
                                'teal' => 'bg-teal-500/15 text-teal-300 border-teal-500/30',
                                'sky', 'blue' => 'bg-sky-500/15 text-sky-300 border-sky-500/30',
                                default => 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold border {{ $badgeClass }}">
                            {{ ucfirst($event['status'] ?? 'Scheduled') }}
                        </span>
                        <span class="text-xs font-semibold text-indigo-400 group-hover:text-white transition flex items-center gap-0.5">
                            Detail
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </li>
            @empty
            <li class="p-8 text-center text-sm text-slate-500">Tidak ada jadwal atau agenda yang tercatat.</li>
            @endforelse
        </ul>
    </div>

    <!-- ANIMATED POPUP MODAL: DATE AGENDA DETAILS -->
    <div x-cloak x-show="dateModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop with animated blur -->
        <div x-show="dateModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-md"
             @click="dateModal = false"></div>

        <!-- Dialog Box with spring pop-in animation -->
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4 sm:p-6" @click.self="dateModal = false">
            <div x-show="dateModal"
                 x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-90 translate-y-4"
                 @click.stop
                 class="relative z-20 w-full max-w-lg rounded-3xl bg-slate-900 border border-slate-700 shadow-2xl overflow-hidden my-auto max-h-[85vh] flex flex-col">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-950/90 border-b border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white tracking-tight" x-text="selectedDateTitle"></h3>
                            <p class="text-xs text-slate-400">
                                <span x-text="selectedEvents.length"></span> agenda & jadwal tercatat
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="dateModal = false" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition text-xl font-bold leading-none">&times;</button>
                </div>

                <!-- Modal Body with events list -->
                <div class="p-6 overflow-y-auto flex-1 space-y-3">
                    <template x-if="selectedEvents.length === 0">
                        <div class="py-12 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-slate-800/80 border border-slate-700/60 mx-auto flex items-center justify-center text-slate-400 mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-300">Tidak ada jadwal pada tanggal ini</p>
                            <p class="text-xs text-slate-500 mt-1">Belum ada deadline tugas, milestone, atau log progres operasional.</p>
                            <div class="mt-5 flex justify-center gap-2">
                                <a href="{{ route('progress.index') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition shadow-lg shadow-indigo-600/30">
                                    + Catat Progres Harian
                                </a>
                            </div>
                        </div>
                    </template>

                    <template x-for="(ev, idx) in selectedEvents" :key="idx">
                        <div class="p-4 rounded-2xl bg-slate-950/90 border border-slate-800 hover:border-slate-700 transition-all space-y-2 group">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                                              :class="{
                                                  'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30': ev.badge_color === 'emerald',
                                                  'bg-rose-500/20 text-rose-300 border border-rose-500/30': ev.badge_color === 'rose',
                                                  'bg-purple-500/20 text-purple-300 border border-purple-500/30': ev.badge_color === 'purple',
                                                  'bg-amber-500/20 text-amber-300 border border-amber-500/30': ev.badge_color === 'amber' || ev.badge_color === 'orange',
                                                  'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30': !['emerald', 'rose', 'purple', 'amber', 'orange'].includes(ev.badge_color)
                                              }"
                                              x-text="ev.category"></span>
                                        <span class="text-[10px] text-slate-500 font-mono" x-show="ev.status" x-text="ev.status"></span>
                                    </div>
                                    <h4 class="text-sm font-bold text-white group-hover:text-indigo-300 transition" x-text="ev.title"></h4>
                                    <p class="text-xs text-slate-400 mt-1" x-show="ev.context" x-text="ev.context"></p>
                                </div>
                                <template x-if="ev.url">
                                    <a :href="ev.url" class="shrink-0 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-indigo-600 text-slate-200 hover:text-white text-xs font-semibold transition flex items-center gap-1">
                                        Buka
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3.5 bg-slate-950/90 border-t border-slate-800 flex justify-between items-center shrink-0 text-xs">
                    <span class="text-slate-500 font-mono" x-text="selectedDate"></span>
                    <button type="button" @click="dateModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
