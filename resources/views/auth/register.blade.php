<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Solvia.Nova OS</title>
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
            <p class="text-xs text-slate-400 mt-1 font-medium">Create your operational account</p>
        </div>

        <!-- Register Card -->
        <div class="rounded-3xl bg-slate-900/90 border border-slate-800/80 p-6 sm:p-8 shadow-2xl backdrop-blur-xl">

            @if($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-rose-950/60 border border-rose-800/60 text-xs text-rose-300 space-y-1">
                    @foreach($errors->all() as $error)
                        <p class="flex items-start gap-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="e.g. Budi Santoso"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="nama@domain.com"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phone (Optional)</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           placeholder="08xxxxxxxxxx"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Role *</label>
                    <select name="role" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        <option value="" disabled selected>Pilih role kamu</option>
                        <option value="backend_developer"  {{ old('role') === 'backend_developer'  ? 'selected' : '' }}>Backend Developer</option>
                        <option value="frontend_developer" {{ old('role') === 'frontend_developer' ? 'selected' : '' }}>Frontend Developer</option>
                        <option value="iot_engineer"       {{ old('role') === 'iot_engineer'       ? 'selected' : '' }}>IoT Engineer</option>
                        <option value="designer"           {{ old('role') === 'designer'           ? 'selected' : '' }}>Designer</option>
                        <option value="content_creator"    {{ old('role') === 'content_creator'    ? 'selected' : '' }}>Content Creator</option>
                        <option value="viewer"             {{ old('role') === 'viewer'             ? 'selected' : '' }}>Viewer (Read-only)</option>
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1">Super Admin hanya bisa dibuat oleh sistem administrator.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password *</label>
                    <input type="password" name="password" required
                           placeholder="Min. 8 karakter"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required
                           placeholder="Ulangi password"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 mt-2 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold text-sm shadow-lg shadow-indigo-500/25 transition">
                    Create Account
                </button>
            </form>

            <p class="text-center text-xs text-slate-500 mt-5">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-semibold">Sign in here</a>
            </p>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6 font-medium">
            Solvia.Nova Operating System v2.0 • Security by Default
        </p>
    </div>

</body>
</html>
