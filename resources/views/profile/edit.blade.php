@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    avatarPreview: '{{ $user->avatar_url ? asset($user->avatar_url) : '' }}',
    handleAvatarPreview(event) {
        const file = event.target.files[0];
        if (file) {
            this.avatarPreview = URL.createObjectURL(file);
        }
    }
}">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2">
                Edit Profil Pengguna
                <span class="text-xs bg-indigo-500/20 text-indigo-300 font-bold px-2 py-0.5 rounded-full uppercase font-mono">Personal Account</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Perbarui informasi identitas diri, kontak, bio, foto profil, dan kata sandi.</p>
        </div>

        <!-- Role Preservation Indicator -->
        <div class="flex items-center gap-2">
            <div class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Role: <strong class="text-white capitalize">{{ str_replace('_', ' ', $user->role) }}</strong> (Terkunci)</span>
            </div>
        </div>
    </div>

    <!-- ROLE PRESERVATION NOTICE BANNER -->
    <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950/30 to-slate-900 border border-slate-800 flex items-start gap-3">
        <div class="p-2 rounded-xl bg-indigo-500/20 text-indigo-400 flex-shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div class="text-xs leading-relaxed text-slate-300">
            <p class="font-bold text-white mb-0.5">Keamanan Hak Akses: Role Akun Terproteksi</p>
            <p class="text-slate-400">
                Setiap pengguna dapat memperbarui biodata dan kredensial pribadi secara bebas. Role operasional Anda (<span class="text-indigo-300 font-semibold uppercase font-mono">{{ $user->role }}</span>) tetap terlindungi dan tidak akan berubah selama pembaruan profil ini.
            </p>
        </div>
    </div>

    <!-- MAIN FORM -->
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- PROFILE PHOTO & BASIC OVERVIEW -->
        <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-slate-400">Foto Profil & Identitas</h3>

            <div class="flex flex-col sm:flex-row items-center gap-6">
                <!-- Avatar Preview -->
                <div class="relative flex-shrink-0">
                    <div class="w-24 h-24 rounded-2xl bg-indigo-600 border-2 border-slate-700 overflow-hidden flex items-center justify-center text-white font-bold text-2xl shadow-xl">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!avatarPreview">
                            <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </template>
                    </div>
                </div>

                <!-- Avatar Upload Inputs -->
                <div class="flex-1 space-y-3 w-full text-xs">
                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Unggah Foto Baru (JPG, PNG, WebP maks 2MB)</label>
                        <input type="file" name="avatar" accept="image/*" @change="handleAvatarPreview($event)" class="w-full text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 transition cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-medium mb-1">Atau Gunakan Tautan URL Gambar</label>
                        <input type="url" name="avatar_url" value="{{ old('avatar_url', $user->avatar_url) }}" placeholder="https://..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-slate-200 text-xs focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- PERSONAL DETAILS -->
        <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-slate-400">Informasi Pribadi</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:ring-1 focus:ring-indigo-500">
                    @error('name')
                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Alamat Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:ring-1 focus:ring-indigo-500">
                    @error('email')
                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nomor WhatsApp / Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+62 8..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:ring-1 focus:ring-indigo-500">
                    @error('phone')
                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Departemen / Divisi</label>
                    <input type="text" name="department" value="{{ old('department', $user->department) }}" placeholder="Design, Engineering, Ops..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:ring-1 focus:ring-indigo-500">
                    @error('department')
                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-300 mb-1">Bio Singkat / Keahlian</label>
                <textarea name="profile_bio" rows="3" placeholder="Tuliskan deskripsi ringkas tentang diri Anda atau fokus keahlian..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:ring-1 focus:ring-indigo-500">{{ old('profile_bio', $user->profile_bio) }}</textarea>
                @error('profile_bio')
                    <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- ROLE & METADATA (READ-ONLY) -->
        <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider text-slate-400">Atribut Akun & Karyawan</h3>
                <span class="text-[11px] text-amber-400 font-medium flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Hanya Dikelola Super Admin
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Peran / Role Sistem</span>
                    <span class="px-2 py-0.5 text-xs font-mono font-bold rounded bg-indigo-500/20 text-indigo-300 uppercase inline-block">
                        {{ $user->role }}
                    </span>
                    <p class="text-[10px] text-slate-500 mt-1">Role tetap tidak berubah saat profil diedit.</p>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Tanggal Bergabung</span>
                    <span class="font-mono text-slate-200 font-bold text-xs block">
                        {{ $user->join_date ? $user->join_date->format('d M Y') : '—' }}
                    </span>
                    <p class="text-[10px] text-slate-500 mt-1">Masa kerja di Solvia.Nova</p>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block mb-1">Status Akun</span>
                    <span class="px-2 py-0.5 text-xs font-mono font-bold rounded bg-emerald-500/20 text-emerald-400 uppercase inline-block">
                        {{ $user->status ?? 'active' }}
                    </span>
                    <p class="text-[10px] text-slate-500 mt-1">Otentikasi aktif</p>
                </div>
            </div>
        </div>

        <!-- CHANGE PASSWORD (OPTIONAL) -->
        <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider text-slate-400">Ubah Kata Sandi (Opsional)</h3>
            <p class="text-xs text-slate-400">Kosongkan jika Anda tidak bermaksud mengubah kata sandi.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password" placeholder="••••••••" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:ring-1 focus:ring-indigo-500">
                    @error('current_password')
                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Password Baru</label>
                    <input type="password" name="new_password" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:ring-1 focus:ring-indigo-500">
                    @error('new_password')
                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" placeholder="Ulangi password baru" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- SUBMIT ACTIONS -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
                Kembali ke Dashboard
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan Profil
            </button>
        </div>
    </form>

</div>
@endsection
