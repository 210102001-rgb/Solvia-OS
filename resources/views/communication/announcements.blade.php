@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ announcementModal: false }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Announcements</h1>
            <p class="text-xs text-slate-400 mt-1">Company-wide communication and important updates</p>
        </div>
        @if($isAdmin)
        <button @click="announcementModal = true" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white shadow-md shadow-indigo-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Announcement
        </button>
        @endif
    </div>

    <!-- ANNOUNCEMENTS FEED -->
    <div class="space-y-4">
        @forelse($announcements as $announcement)
        @php
            $priorityColor = match($announcement->priority) {
                'urgent' => 'border-rose-500/40 bg-rose-950/10',
                'high'   => 'border-amber-500/30',
                default  => 'border-slate-800',
            };
            $priorityBadge = match($announcement->priority) {
                'urgent' => 'bg-rose-500/20 text-rose-300',
                'high'   => 'bg-amber-500/20 text-amber-300',
                default  => 'bg-slate-800 text-slate-400',
            };
            $catColor = match($announcement->category) {
                'urgent'    => 'bg-rose-500/20 text-rose-300',
                'company'   => 'bg-indigo-500/20 text-indigo-300',
                'event'     => 'bg-purple-500/20 text-purple-300',
                'technical' => 'bg-cyan-500/20 text-cyan-300',
                default     => 'bg-slate-800 text-slate-300',
            };
            $isRead = $announcement->reads->where('user_id', Auth::id())->isNotEmpty();
        @endphp
        <div class="p-5 rounded-2xl bg-slate-900/90 border {{ $priorityColor }} shadow-md" x-data="{ expanded: false }">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $catColor }}">{{ $announcement->category }}</span>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $priorityBadge }}">{{ $announcement->priority }}</span>
                        @if(!$isRead)
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-indigo-500/20 text-indigo-300">New</span>
                        @endif
                    </div>
                    <h2 class="font-bold text-base text-white">{{ $announcement->title }}</h2>
                    <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-2">
                        <span>{{ $announcement->author->name ?? 'System' }}</span>
                        <span class="text-slate-600">•</span>
                        <span>{{ $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->diffForHumans() : $announcement->created_at->diffForHumans() }}</span>
                        <span class="text-slate-600">•</span>
                        <span class="capitalize">{{ str_replace(['_','-'], ' ', $announcement->audience_type) }}</span>
                    </p>
                </div>
                @if($isRead)
                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @endif
            </div>

            <div class="mt-3 text-xs text-slate-300 leading-relaxed" :class="expanded ? '' : 'line-clamp-3'">
                {!! nl2br(e($announcement->content)) !!}
            </div>

            <div class="mt-3 flex items-center gap-3">
                <button @click="expanded = !expanded" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition">
                    <span x-show="!expanded">Read more</span>
                    <span x-show="expanded" x-cloak>Show less</span>
                </button>
                @if(!$isRead)
                <form action="{{ route('communication.announcements.read', $announcement->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-slate-500 hover:text-slate-300 transition">Mark as read</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="p-12 text-center rounded-2xl bg-slate-900 border border-slate-800">
            <svg class="mx-auto w-12 h-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            <p class="text-sm font-medium text-white">No announcements yet</p>
            <p class="text-xs text-slate-500 mt-1">
                @if($isAdmin) Create the first announcement using the button above. @else No announcements at this time. @endif
            </p>
        </div>
        @endforelse
    </div>

    <div>{{ $announcements->links() }}</div>

    <!-- MODAL: NEW ANNOUNCEMENT (Super Admin only) -->
    @if($isAdmin)
    <div x-cloak x-show="announcementModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="announcementModal" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="announcementModal = false"></div>
        <div class="relative z-10 flex min-h-screen items-center justify-center p-4" @click.self="announcementModal = false">
            <div x-show="announcementModal" x-transition @click.stop class="relative z-20 w-full max-w-2xl rounded-2xl bg-slate-900 border border-slate-700 p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                    <h3 class="font-bold text-base text-white">Broadcast Announcement</h3>
                    <button @click="announcementModal = false" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('communication.announcements.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Title *</label>
                        <input type="text" name="title" required placeholder="Announcement title" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Content *</label>
                        <textarea name="content" rows="5" required placeholder="Write the announcement content..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Category *</label>
                            <select name="category" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="company">Company</option>
                                <option value="urgent">Urgent</option>
                                <option value="event">Event</option>
                                <option value="policy">Policy</option>
                                <option value="technical">Technical</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Priority *</label>
                            <select name="priority" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Audience *</label>
                            <select name="audience_type" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                                <option value="all">All Users</option>
                                <option value="role">By Role</option>
                                <option value="team">By Team</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Audience Target</label>
                            <input type="text" name="audience_target" placeholder="e.g. backend_developer or team ID" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Publish Date *</label>
                            <input type="date" name="publish_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Expiry Date</label>
                            <input type="date" name="expiry_date" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" @click="announcementModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/20">Broadcast</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
