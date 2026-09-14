<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#020617">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Solvia.Nova OS' }} — Operating System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
        /* Mobile Touch & Safe Area */
        .touch-scroll { -webkit-overflow-scrolling: touch; }
        .safe-pb { padding-bottom: max(1rem, env(safe-area-inset-bottom, 1rem)); }
    </style>
</head>
<body class="h-full antialiased flex flex-col md:flex-row overflow-hidden bg-slate-950 text-slate-100 touch-manipulation"
      x-data="{
          mobileDrawer: false,
          drawerSearch: '',
          mobileFinanceOpen: false,
          mobileResourceOpen: false,
          mobileCompanyOpen: false,
          searchOpen: false,
          searchQuery: '',
          searchResults: [],
          async performSearch() {
              if (this.searchQuery.length < 2) { this.searchResults = []; return; }
              const res = await fetch(`/api/search?q=${encodeURIComponent(this.searchQuery)}`);
              this.searchResults = await res.json();
          }
      }"
      @keydown.window.ctrl.k.prevent="searchOpen = true"
      @keydown.window.slash.prevent="searchOpen = true">

    <!-- DESKTOP SIDEBAR -->
    <aside class="hidden md:flex md:w-64 lg:w-72 flex-col flex-shrink-0 border-r border-slate-800/80 bg-slate-900/90 backdrop-blur-xl select-none z-30">
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800/80">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center font-bold text-white text-lg shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition">
                    SN
                </div>
                <div>
                    <span class="font-extrabold text-base tracking-tight text-white flex items-center gap-1.5">
                        Solvia<span class="text-indigo-400">.Nova</span>
                        <span class="text-[10px] uppercase font-bold tracking-widest bg-indigo-500/20 text-indigo-300 px-1.5 py-0.5 rounded">OS</span>
                    </span>
                    <span class="text-[10px] text-slate-400 block font-medium">Business Operating System</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 text-xs">
            <!-- Main Section -->
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Core</p>
                <div class="space-y-0.5">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Command Center
                    </a>
                    <a href="{{ route('projects.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('projects.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Projects & Tasks
                    </a>
                    <a href="{{ route('progress.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('progress.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Daily Progress
                    </a>
                    <a href="{{ route('blockers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('blockers.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Blockers
                    </a>
                    <a href="{{ route('schedule.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('schedule.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Schedule & Calendar
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('profile.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Edit Profil Saya
                    </a>
                </div>
            </div>

            <!-- Finance Hub -->
            <!-- Financial Control System (Finance Hub) -->
            @if(Auth::user()->isSuperAdmin())
            <div x-data="{ financeOpen: true }">
                <div class="flex items-center justify-between px-3 mb-1.5 cursor-pointer" @click="financeOpen = !financeOpen">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Financial Control</p>
                    <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': financeOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div x-show="financeOpen" x-collapse class="space-y-0.5">
                    <a href="{{ route('finance.dashboard') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('finance.accounts') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.accounts*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Accounts
                    </a>
                    <a href="{{ route('finance.transactions') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.transactions*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Transactions
                    </a>
                    <a href="{{ route('finance.revenue') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.revenue') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        Revenue
                    </a>
                    <a href="{{ route('finance.expenses') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.expenses') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                        Expenses
                    </a>
                    <a href="{{ route('finance.invoices') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.invoices') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Invoices
                    </a>
                    <a href="{{ route('finance.payments') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.payments*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Payments
                    </a>
                    <a href="{{ route('finance.receivables') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.receivables') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Receivables
                    </a>
                    <a href="{{ route('finance.payables') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.payables*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Payables
                    </a>
                    <a href="{{ route('finance.cashflow') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.cashflow') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        Cashflow
                    </a>
                    <a href="{{ route('finance.budgets') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.budgets') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                        Budget
                    </a>
                    <a href="{{ route('finance.payroll') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.payroll') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Payroll
                    </a>
                    <a href="{{ route('finance.reimbursements') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.reimbursements') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5 5l-2 2-3-3m9 4h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Reimbursement
                    </a>
                    <a href="{{ route('finance.project_finance') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.project_finance') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Project Finance
                    </a>
                    <a href="{{ route('finance.recurring_cost') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.recurring_cost') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Recurring Cost
                    </a>
                    <a href="{{ route('finance.asset_finance') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.asset_finance') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Asset Finance
                    </a>
                    <a href="{{ route('finance.reports') }}" class="flex items-center gap-2.5 px-3 py-1.5 text-xs rounded-lg font-medium transition {{ request()->routeIs('finance.reports') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Financial Reports
                    </a>
                </div>
            </div>
            @endif

            <!-- Resources & Assets -->
            @if(Auth::user()->isSuperAdmin())
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Resource Registry</p>
                <div class="space-y-0.5">
                    <a href="{{ route('resources.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('resources.index') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        All Resources
                    </a>
                    <a href="{{ route('resources.assets') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('resources.assets*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Physical Assets
                    </a>
                    <a href="{{ route('resources.infrastructure') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('resources.infrastructure') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                        VPS, Domain & SSL
                    </a>
                    <a href="{{ route('resources.subscriptions') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('resources.subscriptions') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Subscriptions & SaaS
                    </a>
                    <a href="{{ route('resources.inventory') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('resources.inventory') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Inventory & Stock
                    </a>
                    <a href="{{ route('resources.contracts') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('resources.contracts') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Contracts
                    </a>
                    <a href="{{ route('resources.accounts') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('resources.accounts') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Company Accounts
                    </a>
                    <a href="{{ route('resources.licenses') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('resources.licenses') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Licenses
                    </a>
                </div>
            </div>
            @else
            <!-- Resources & Assets (Non-Admin View: Hanya Fokus Asset Pribadi) -->
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Equipment</p>
                <div class="space-y-0.5">
                    <a href="{{ route('resources.assets') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('resources.assets*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        My Assets
                    </a>
                </div>
            </div>
            @endif

            <!-- Purchasing & Operations -->
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Operations</p>
                <div class="space-y-0.5">
                    <a href="{{ route('purchasing.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('purchasing.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Purchasing & Approvals
                    </a>
                    <a href="{{ route('finance.reimbursements') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('finance.reimbursements*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5 5l-2 2-3-3m9 4h.01M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Reimbursements
                    </a>
                    <a href="{{ route('communication.announcements') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('communication.announcements*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        Announcements
                    </a>
                    <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('documents.index') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Documents
                    </a>
                    <a href="{{ route('documents.kb') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('documents.kb*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Knowledge Base
                    </a>
                </div>
            </div>

            <!-- Company & System -->
            @if(Auth::user()->isSuperAdmin())
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Company & System</p>
                <div class="space-y-0.5">
                    <a href="{{ route('company.profile') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('company.profile') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Company Profile
                    </a>
                    <a href="{{ route('company.users') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('company.users') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Users & Roles
                    </a>
                    <a href="{{ route('company.teams') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('company.teams') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Teams
                    </a>
                    <a href="{{ route('company.clients') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('company.clients') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Clients
                    </a>
                    <a href="{{ route('automation.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('automation.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Automation Engine
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('reports.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Reports
                    </a>
                    <a href="{{ route('audit.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition {{ request()->routeIs('audit.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Audit Trail
                    </a>
                </div>
            </div>
            @endif
        </nav>

        <!-- User Profile Footer -->
        <div class="p-3 border-t border-slate-800/80 bg-slate-950/50">
            <a href="{{ route('profile.edit') }}" title="Edit Profil Saya" class="flex items-center gap-2.5 p-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-indigo-500/50 hover:bg-slate-800/50 transition group">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 overflow-hidden flex items-center justify-center font-bold text-xs text-white flex-shrink-0 group-hover:scale-105 transition">
                    @if(Auth::user()->avatar_url)
                        <img src="{{ asset(Auth::user()->avatar_url) }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 object-cover">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-white truncate group-hover:text-indigo-300 transition">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate capitalize">{{ str_replace('_', ' ', Auth::user()->role) }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-400 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </a>
        </div>
    </aside>

    <!-- MAIN VIEW WRAPPER -->
    <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-950">
        <!-- TOPBAR / MOBILE NATIVE APP HEADER -->
        <header class="h-14 sm:h-16 border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-2xl flex items-center justify-between px-3 sm:px-6 z-20 flex-shrink-0">
            <!-- Mobile App Brand & Drawer Trigger -->
            <div class="flex items-center gap-2.5 md:hidden">
                <button @click="mobileDrawer = true" class="p-2 -ml-1 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800/80 active:scale-95 transition" aria-label="Open App Hub">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-xs shadow-md shadow-indigo-500/30">
                        SN
                    </div>
                    <span class="font-extrabold text-sm tracking-tight text-white flex items-center gap-1">
                        Solvia<span class="text-indigo-400">.Nova</span>
                        <span class="text-[8px] uppercase font-black bg-indigo-500/20 text-indigo-300 px-1 py-0.5 rounded font-mono">OS</span>
                    </span>
                </a>
            </div>

            <!-- Global Search Trigger (Desktop) -->
            <div class="flex-1 max-w-md hidden md:block">
                <button @click="searchOpen = true" class="w-full flex items-center justify-between px-3.5 py-1.5 text-xs text-slate-400 bg-slate-950/80 border border-slate-800 rounded-xl hover:border-indigo-500/50 hover:text-slate-300 transition shadow-inner">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Search Solvia.Nova...</span>
                    </span>
                    <kbd class="px-1.5 py-0.5 text-[10px] font-mono bg-slate-800 border border-slate-700 rounded text-slate-400">Ctrl+K</kbd>
                </button>
            </div>

            <!-- Topbar Right Actions (Mobile & Desktop) -->
            <div class="flex items-center gap-1.5 sm:gap-3">
                <!-- Mobile Search Icon -->
                <button @click="searchOpen = true" class="md:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 active:scale-95 transition" aria-label="Search">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>

                <!-- Notifications Bell -->
                <a href="{{ route('communication.notifications') }}" class="relative p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 active:scale-95 transition" aria-label="Notifications">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @php $unreadCount = Auth::user()->unreadNotifications()->count(); @endphp
                    @if($unreadCount > 0)
                        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full bg-indigo-500 ring-2 ring-slate-900 animate-pulse"></span>
                    @endif
                </a>

                <!-- User Profile Thumbnail (Mobile & Desktop) -->
                <a href="{{ route('profile.edit') }}" title="Edit Profil Saya" class="flex items-center gap-2 p-1 sm:px-2.5 sm:py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-indigo-500/50 text-slate-300 hover:text-white text-xs font-medium transition group active:scale-95">
                    <div class="w-7 h-7 rounded-lg bg-indigo-600 overflow-hidden flex items-center justify-center font-bold text-[11px] text-white flex-shrink-0">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ asset(Auth::user()->avatar_url) }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 object-cover">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        @endif
                    </div>
                    <span class="hidden sm:inline capitalize">{{ str_replace('_', ' ', Auth::user()->role) }}</span>
                    <span class="hidden sm:inline text-[10px] text-indigo-400 font-bold ml-1 group-hover:underline">Edit</span>
                </a>

                <!-- Desktop Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="hidden md:inline">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-400 rounded-xl hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <!-- FLASH MESSAGES -->
        @if(session('success'))
            <div class="bg-emerald-950/80 border-b border-emerald-800/80 px-4 py-2.5 text-xs text-emerald-300 flex items-center justify-between z-10" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-400 hover:text-emerald-200 text-sm font-bold">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-950/80 border-b border-rose-800/80 px-4 py-2.5 text-xs text-rose-300 flex items-center justify-between z-10" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-rose-400 hover:text-rose-200 text-sm font-bold">&times;</button>
            </div>
        @endif
        @if(isset($errors) && $errors->any())
            <div class="bg-rose-950/80 border-b border-rose-800/80 px-4 py-2 text-xs text-rose-300 z-10">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- SCROLLABLE VIEW CONTENT (Optimized for full mobile touch) -->
        <main class="flex-1 overflow-y-auto pb-28 md:pb-6 p-3.5 sm:p-6 lg:p-8 touch-scroll">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- FULL NATIVE MOBILE BOTTOM TAB BAR -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-slate-950/90 backdrop-blur-2xl border-t border-slate-800/80 grid grid-cols-5 items-center px-1 z-40 shadow-2xl safe-pb">
            <!-- 1. Home -->
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-1 transition active:scale-90 {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="p-1 rounded-xl {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'bg-indigo-500/20 text-indigo-300 shadow-sm shadow-indigo-500/30' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span class="text-[9px] tracking-tight mt-0.5">Home</span>
            </a>

            <!-- 2. Projects -->
            <a href="{{ route('projects.index') }}" class="flex flex-col items-center justify-center py-1 transition active:scale-90 {{ request()->routeIs('projects.*') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="p-1 rounded-xl {{ request()->routeIs('projects.*') ? 'bg-indigo-500/20 text-indigo-300 shadow-sm shadow-indigo-500/30' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
                <span class="text-[9px] tracking-tight mt-0.5">Projects</span>
            </a>

            <!-- 3. Schedule -->
            <a href="{{ route('schedule.index') }}" class="flex flex-col items-center justify-center py-1 transition active:scale-90 {{ request()->routeIs('schedule.*') ? 'text-indigo-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="p-1 rounded-xl {{ request()->routeIs('schedule.*') ? 'bg-indigo-500/20 text-indigo-300 shadow-sm shadow-indigo-500/30' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-[9px] tracking-tight mt-0.5">Schedule</span>
            </a>

            <!-- 4. Finance (Super Admin) or Daily Progress (Operational) -->
            @if(Auth::user()->isSuperAdmin())
            <a href="{{ route('finance.dashboard') }}" class="flex flex-col items-center justify-center py-1 transition active:scale-90 {{ request()->routeIs('finance.*') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="p-1 rounded-xl {{ request()->routeIs('finance.*') ? 'bg-emerald-500/20 text-emerald-300 shadow-sm shadow-emerald-500/30' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[9px] tracking-tight mt-0.5">Finance</span>
            </a>
            @else
            <a href="{{ route('progress.index') }}" class="flex flex-col items-center justify-center py-1 transition active:scale-90 {{ request()->routeIs('progress.*') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="p-1 rounded-xl {{ request()->routeIs('progress.*') ? 'bg-emerald-500/20 text-emerald-300 shadow-sm shadow-emerald-500/30' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <span class="text-[9px] tracking-tight mt-0.5">Progress</span>
            </a>
            @endif

            <!-- 5. App Hub / More -->
            <button @click="mobileDrawer = true" class="flex flex-col items-center justify-center py-1 transition active:scale-90 text-slate-400 hover:text-white" aria-label="Open App Launcher">
                <div class="p-1 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </div>
                <span class="text-[9px] tracking-tight mt-0.5">App Hub</span>
            </button>
        </nav>
    </div>

    <!-- FULL MOBILE APP DRAWER / LAUNCHER (Slide-Over Hub) -->
    <div x-cloak x-show="mobileDrawer" class="relative z-50 md:hidden" role="dialog" aria-modal="true">
        <div x-show="mobileDrawer" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-950/85 backdrop-blur-md" @click="mobileDrawer = false"></div>

        <div x-show="mobileDrawer" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 max-w-[85vw] sm:max-w-sm w-full bg-slate-900 border-r border-slate-800 p-4 sm:p-5 overflow-y-auto flex flex-col justify-between shadow-2xl">
            <div class="space-y-5">
                <!-- Drawer Topbar -->
                <div class="flex items-center justify-between pb-3.5 border-b border-slate-800">
                    <div class="font-black text-sm tracking-tight text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xs font-bold text-white shadow-md shadow-indigo-500/30">
                            SN
                        </div>
                        <div>
                            <span>Solvia<span class="text-indigo-400">.Nova</span></span>
                            <span class="text-[9px] uppercase font-mono font-bold bg-indigo-500/20 text-indigo-300 px-1 py-0.5 rounded ml-1">OS</span>
                        </div>
                    </div>
                    <button @click="mobileDrawer = false" class="p-1.5 rounded-xl bg-slate-800 text-slate-400 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- User Profile Header Card -->
                <div class="p-3.5 rounded-2xl bg-gradient-to-r from-slate-950 to-indigo-950/30 border border-slate-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 overflow-hidden flex items-center justify-center font-bold text-sm text-white flex-shrink-0 shadow-md">
                            @if(Auth::user()->avatar_url)
                                <img src="{{ asset(Auth::user()->avatar_url) }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 object-cover">
                            @else
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</p>
                            <span class="px-1.5 py-0.5 text-[9px] font-mono font-bold uppercase rounded bg-indigo-500/20 text-indigo-300 inline-block mt-0.5">
                                {{ Auth::user()->role }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}" @click="mobileDrawer = false" class="px-2.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-bold shadow-md shadow-indigo-600/30 transition flex-shrink-0">
                        Edit
                    </a>
                </div>

                <!-- Search Inside Mobile Drawer -->
                <div>
                    <input type="text" x-model="drawerSearch" placeholder="Filter menu aplikasi..." class="w-full px-3 py-2 text-xs rounded-xl bg-slate-950 border border-slate-800 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <!-- Categorized App Sections -->
                <div class="space-y-4 text-xs">
                    <!-- Core Section -->
                    <div x-show="!drawerSearch || 'command center projects progress blockers schedule calendar profil'.includes(drawerSearch.toLowerCase())">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Core Workspace
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('dashboard') }}" @click="mobileDrawer = false" class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 font-medium text-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                <span class="truncate">Command</span>
                            </a>
                            <a href="{{ route('projects.index') }}" @click="mobileDrawer = false" class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 font-medium text-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-sky-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                <span class="truncate">Projects</span>
                            </a>
                            <a href="{{ route('progress.index') }}" @click="mobileDrawer = false" class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 font-medium text-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span class="truncate">Progress</span>
                            </a>
                            <a href="{{ route('blockers.index') }}" @click="mobileDrawer = false" class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 font-medium text-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span class="truncate">Blockers</span>
                            </a>
                            <a href="{{ route('schedule.index') }}" @click="mobileDrawer = false" class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 font-medium text-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="truncate">Schedule</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" @click="mobileDrawer = false" class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 font-medium text-slate-200 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="truncate">Profil Saya</span>
                            </a>
                        </div>
                    </div>

                    <!-- Financial Control (Super Admin Only) -->
                    @if(Auth::user()->isSuperAdmin())
                    <div x-show="!drawerSearch || 'finance keuangan kas rekening transaksi invoice revenue hutang piutang payroll budget laba rugi reports'.includes(drawerSearch.toLowerCase())">
                        <div class="flex items-center justify-between mb-2 cursor-pointer" @click="mobileFinanceOpen = !mobileFinanceOpen">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Financial Control
                            </p>
                            <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': mobileFinanceOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="mobileFinanceOpen || drawerSearch" class="grid grid-cols-2 gap-2">
                            <a href="{{ route('finance.dashboard') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Dashboard
                            </a>
                            <a href="{{ route('finance.accounts') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span> Accounts
                            </a>
                            <a href="{{ route('finance.transactions') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span> Buku Besar
                            </a>
                            <a href="{{ route('finance.revenue') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Revenue
                            </a>
                            <a href="{{ route('finance.expenses') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Expenses
                            </a>
                            <a href="{{ route('finance.invoices') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Invoices
                            </a>
                            <a href="{{ route('finance.payments') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Payments
                            </a>
                            <a href="{{ route('finance.receivables') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span> Piutang
                            </a>
                            <a href="{{ route('finance.payables') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Hutang
                            </a>
                            <a href="{{ route('finance.cashflow') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span> Cashflow
                            </a>
                            <a href="{{ route('finance.budgets') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span> Budgets
                            </a>
                            <a href="{{ route('finance.payroll') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Payroll
                            </a>
                            <a href="{{ route('finance.project_finance') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> Project Margin
                            </a>
                            <a href="{{ route('finance.recurring_cost') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> MRC & ARC
                            </a>
                            <a href="{{ route('finance.asset_finance') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Capex Aset
                            </a>
                            <a href="{{ route('finance.reports') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Reports
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Resources Section -->
                    <div x-show="!drawerSearch || 'resources asset infrastruktur vps subscription lisensi kontrak barang gudang inventory'.includes(drawerSearch.toLowerCase())">
                        <div class="flex items-center justify-between mb-2 cursor-pointer" @click="mobileResourceOpen = !mobileResourceOpen">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Resource Registry
                            </p>
                            <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': mobileResourceOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="mobileResourceOpen || drawerSearch" class="grid grid-cols-2 gap-2">
                            <a href="{{ route('resources.assets') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Assets
                            </a>
                            <a href="{{ route('resources.infrastructure') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span> Server / VPS
                            </a>
                            <a href="{{ route('resources.subscriptions') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span> Subscriptions
                            </a>
                            <a href="{{ route('resources.inventory') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span> Inventory
                            </a>
                            <a href="{{ route('resources.contracts') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Contracts
                            </a>
                            <a href="{{ route('resources.licenses') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Licenses
                            </a>
                        </div>
                    </div>

                    <!-- Company & Governance -->
                    <div x-show="!drawerSearch || 'company perusahaan user anggota tim client purchasing beli pengumuman artikel kb audit'.includes(drawerSearch.toLowerCase())">
                        <div class="flex items-center justify-between mb-2 cursor-pointer" @click="mobileCompanyOpen = !mobileCompanyOpen">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span> Tata Kelola
                            </p>
                            <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': mobileCompanyOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="mobileCompanyOpen || drawerSearch" class="grid grid-cols-2 gap-2">
                            @if(Auth::user()->isSuperAdmin())
                            <a href="{{ route('company.users') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span> Users
                            </a>
                            <a href="{{ route('company.teams') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span> Teams
                            </a>
                            <a href="{{ route('company.clients') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span> Clients
                            </a>
                            <a href="{{ route('audit.index') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Audit Trail
                            </a>
                            @endif
                            <a href="{{ route('purchasing.index') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span> Purchasing
                            </a>
                            <a href="{{ route('communication.announcements') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> Pengumuman
                            </a>
                            <a href="{{ route('documents.kb') }}" @click="mobileDrawer = false" class="p-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 hover:text-white flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Knowledge Base
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="pt-4 border-t border-slate-800/80 space-y-3">
                <div class="flex items-center justify-between text-[11px] text-slate-500">
                    <span>Solvia.Nova OS v2.4</span>
                    <span class="text-emerald-400 font-mono flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Mobile Mode Active
                    </span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 text-xs font-bold transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout dari Perangkat
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- GLOBAL SPOTLIGHT SEARCH MODAL (Section BA) -->
    <div x-cloak x-show="searchOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div x-show="searchOpen" x-transition.opacity class="fixed inset-0 bg-slate-950/80 backdrop-blur-md" @click="searchOpen = false"></div>
        <div class="relative z-10 flex min-h-screen items-start justify-center p-4 sm:p-6 md:p-20" @click.self="searchOpen = false">
            <div x-show="searchOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 @click.stop class="relative z-20 w-full max-w-xl transform divide-y divide-slate-800 overflow-hidden rounded-2xl bg-slate-900 border border-slate-700/80 shadow-2xl transition-all">
                <div class="relative flex items-center px-4">
                    <svg class="w-5 h-5 text-slate-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="searchQuery" @input.debounce.250ms="performSearch()"
                           class="h-14 w-full bg-transparent border-0 text-sm text-white placeholder-slate-400 focus:outline-none focus:ring-0"
                           placeholder="Type to search projects, tasks, clients, assets, invoices..." autofocus>
                    <kbd class="px-2 py-1 text-[10px] font-mono bg-slate-800 border border-slate-700 rounded text-slate-400">ESC</kbd>
                </div>

                <div x-show="searchResults.length > 0" class="max-h-80 overflow-y-auto p-2 divide-y divide-slate-800/50">
                    <template x-for="item in searchResults" :key="item.url + item.title">
                        <a :href="item.url" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-800/80 transition group">
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest block" x-text="item.category"></span>
                                <p class="text-sm font-semibold text-white group-hover:text-indigo-300 transition truncate" x-text="item.title"></p>
                                <p class="text-xs text-slate-400 truncate" x-text="item.subtitle"></p>
                            </div>
                            <svg class="w-4 h-4 text-slate-500 group-hover:text-white transition transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </template>
                </div>

                <div x-show="searchQuery.length >= 2 && searchResults.length === 0" class="p-8 text-center text-xs text-slate-400">
                    No results found matching "<span class="text-white font-medium" x-text="searchQuery"></span>".
                </div>

                <div class="px-4 py-2.5 bg-slate-950/60 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Press <kbd class="font-mono bg-slate-800 px-1 py-0.5 rounded text-slate-300">ESC</kbd> to close</span>
                    <span>Permission-aware search</span>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
