@extends('layouts.app')

@section('content')
@php
    $health = $profile->settings['health'] ?? [];
@endphp
<div class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white">Company Profile</h1>
        <p class="text-xs text-slate-400 mt-1">Legal identity, contact, currency, and configurable Project Health thresholds.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-4">
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
                <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-black text-white text-lg mb-3">SN</div>
                <h2 class="text-lg font-bold text-white">{{ $profile->name }}</h2>
                <p class="text-xs text-slate-400 mt-1">{{ $profile->legal_name }}</p>
                <dl class="mt-4 space-y-2 text-xs">
                    <div><dt class="text-slate-500 uppercase tracking-wider">Email</dt><dd class="text-slate-200">{{ $profile->email }}</dd></div>
                    <div><dt class="text-slate-500 uppercase tracking-wider">Phone</dt><dd class="text-slate-200">{{ $profile->phone }}</dd></div>
                    <div><dt class="text-slate-500 uppercase tracking-wider">Website</dt><dd class="text-indigo-300">{{ $profile->website }}</dd></div>
                    <div><dt class="text-slate-500 uppercase tracking-wider">NPWP</dt><dd class="text-slate-200 font-mono">{{ $profile->tax_number }}</dd></div>
                    <div><dt class="text-slate-500 uppercase tracking-wider">Currency</dt><dd class="text-slate-200">{{ $profile->currency }}</dd></div>
                </dl>
            </div>
        </div>

        <form action="{{ route('company.profile.update') }}" method="POST" class="lg:col-span-2 p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-5">
            @csrf
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Update identity</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block text-slate-400 mb-1">Company name</label>
                    <input type="text" name="name" value="{{ old('name', $profile->name) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Legal name</label>
                    <input type="text" name="legal_name" value="{{ old('legal_name', $profile->legal_name) }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $profile->email) }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $profile->phone) }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Website</label>
                    <input type="text" name="website" value="{{ old('website', $profile->website) }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Tax number</label>
                    <input type="text" name="tax_number" value="{{ old('tax_number', $profile->tax_number) }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">{{ old('address', $profile->address) }}</textarea>
                </div>
            </div>

            <h3 class="text-sm font-bold text-white uppercase tracking-wider pt-2">Project Health thresholds</h3>
            <p class="text-[11px] text-slate-500">These numbers drive ON TRACK / AT RISK / OFF TRACK. Dates stay on the project; this only configures evaluation.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block text-slate-400 mb-1">Overdue tasks → Off Track</label>
                    <input type="number" min="1" name="health_overdue_off_track" value="{{ old('health_overdue_off_track', $health['overdue_tasks_off_track'] ?? 3) }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Progress gap % → Off Track</label>
                    <input type="number" min="1" name="health_progress_gap_off_track" value="{{ old('health_progress_gap_off_track', $health['progress_gap_off_track'] ?? 30) }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Progress gap % → At Risk</label>
                    <input type="number" min="1" name="health_progress_gap_at_risk" value="{{ old('health_progress_gap_at_risk', $health['progress_gap_at_risk'] ?? 15) }}" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                </div>
            </div>

            <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold">Save company profile</button>
        </form>
    </div>
</div>
@endsection
