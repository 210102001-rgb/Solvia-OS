<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Solvia.Nova OS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-full flex flex-col justify-center items-center p-4 sm:p-6 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-950/40 via-slate-950 to-slate-950">

    <div class="w-full max-w-md">

        <!-- Logo & Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 items-center justify-center font-black text-white text-2xl shadow-xl shadow-indigo-500/25 mb-4">
                SN
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white flex items-center justify-center gap-2">
                Solvia<span class="text-indigo-400">.Nova</span>
                <span class="text-xs uppercase font-bold tracking-widest bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded-full">OS</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Internal Business Operating System • Single Source of Truth</p>
        </div>

        <!-- Login Card -->
        <div class="rounded-3xl bg-slate-900/90 border border-slate-800/80 p-6 sm:p-8 shadow-2xl backdrop-blur-xl">

            @if($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-rose-950/60 border border-rose-800/60 text-xs text-rose-300 flex items-start gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3 rounded-xl bg-emerald-950/60 border border-emerald-800/60 text-xs text-emerald-300 flex items-start gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="email@domain.com"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 text-slate-400 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0 focus:ring-offset-0">
                        Remember session
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold text-sm shadow-lg shadow-indigo-500/25 transition">
                    Sign In to OS
                </button>
            </form>

                <p class="text-center text-xs text-slate-500 mt-6 pt-5 border-t border-slate-800/80">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold">Daftar di sini</a>
                </p>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6 font-medium">
            Solvia.Nova Operating System v2.0 • Security by Default
        </p>
    </div>

</body>
</html>
