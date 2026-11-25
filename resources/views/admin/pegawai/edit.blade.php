@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit Pegawai" parentTitle="Pegawai" :parentUrl="route('pegawai.index')" />

<div class="space-y-6">

    <x-common.component-card title="Form Edit Pegawai">

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                <p class="font-bold">Mohon lengkapi atau perbaiki data berikut:</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="formPegawaiEdit" action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" novalidate>

            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Nama Pegawai --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Pegawai <span class="text-red-500">*</span>
                    </label>

                    <input type="text" name="nama" value="{{ old('nama', $pegawai->nama) }}" required maxlength="100"
                        class="h-12 w-full rounded-lg border {{ $errors->has('nama') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:ring-brand-500">

                    @error('nama')
                        <small class="mt-1 block text-red-500">{{ $message }}</small>
                    @enderror
                    <small class="mt-1 hidden text-red-500" data-error-for="nama"></small>
                </div>

                {{-- Nomor HP --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nomor HP <span class="text-red-500">*</span>
                    </label>

                    <input type="tel" name="no_hp" value="{{ old('no_hp', $pegawai->no_hp) }}" required maxlength="20"
                        class="h-12 w-full rounded-lg border {{ $errors->has('no_hp') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:ring-brand-500">

                    @error('no_hp')
                        <small class="mt-1 block text-red-500">{{ $message }}</small>
                    @enderror
                    <small class="mt-1 hidden text-red-500" data-error-for="no_hp"></small>
                </div>

                {{-- Username --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="username" value="{{ old('username', optional($pegawai->user)->username) }}" required maxlength="255"
                        class="h-12 w-full rounded-lg border {{ $errors->has('username') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    @error('username')
                        <small class="mt-1 block text-red-500">{{ $message }}</small>
                    @enderror
                    <small class="mt-1 hidden text-red-500" data-error-for="username"></small>
                </div>

                {{-- Email --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', optional($pegawai->user)->email) }}" required maxlength="255"
                        class="h-12 w-full rounded-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    @error('email')
                        <small class="mt-1 block text-red-500">{{ $message }}</small>
                    @enderror
                    <small class="mt-1 hidden text-red-500" data-error-for="email"></small>
                </div>

                {{-- Password --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password baru (kosongkan kalau tidak diganti)
                    </label>
                    <input type="password" name="password" minlength="6"
                        placeholder="Minimal 6 karakter"
                        class="h-12 w-full rounded-lg border {{ $errors->has('password') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:ring-brand-500">
                    @error('password')
                        <small class="mt-1 block text-red-500">{{ $message }}</small>
                    @enderror
                    <small class="mt-1 hidden text-red-500" data-error-for="password"></small>
                </div>

                {{-- Jabatan --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jabatan <span class="text-red-500">*</span>
                    </label>

                    <select name="jabatan" required
                        class="h-12 w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white dark:bg-gray-800">

                        <option value="">Pilih Jabatan</option>

                        @foreach ($roles as $role)
                            <option value="{{ $role }}" {{ old('jabatan', $pegawai->jabatan) == $role ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach

                    </select>

                    @error('jabatan')
                        <small class="mt-1 block text-red-500">{{ $message }}</small>
                    @enderror
                    <small class="mt-1 hidden text-red-500" data-error-for="jabatan"></small>
                </div>

                {{-- Tanggal Masuk --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Masuk <span class="text-red-500">*</span>
                    </label>

                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', $pegawai->tanggal_masuk) }}"
                        max="{{ date('Y-m-d') }}" required
                        class="h-12 w-full rounded-lg border {{ $errors->has('tanggal_masuk') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:ring-brand-500">

                    @error('tanggal_masuk')
                        <small class="mt-1 block text-red-500">{{ $message }}</small>
                    @enderror
                    <small class="mt-1 hidden text-red-500" data-error-for="tanggal_masuk"></small>
                </div>

                {{-- Status --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Aktif
                    </label>

                    <select name="status_aktif"
                        class="h-12 w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:text-white dark:bg-gray-800">

                        <option value="1" {{ (string) old('status_aktif', (int) $pegawai->status_aktif) === '1' ? 'selected' : '' }}>
                            Aktif
                        </option>

                        <option value="0" {{ (string) old('status_aktif', (int) $pegawai->status_aktif) === '0' ? 'selected' : '' }}>
                            Tidak Aktif
                        </option>

                    </select>

                    @error('status_aktif')
                        <small class="mt-1 block text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Alamat <span class="text-red-500">*</span>
                    </label>

                    <textarea name="alamat" rows="3" required
                        class="w-full rounded-lg border {{ $errors->has('alamat') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 px-4 py-3 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:ring-brand-500">{{ old('alamat', $pegawai->alamat) }}</textarea>

                    @error('alamat')
                        <small class="mt-1 block text-red-500">{{ $message }}</small>
                    @enderror
                    <small class="mt-1 hidden text-red-500" data-error-for="alamat"></small>
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3">

                <a href="{{ route('pegawai.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Batal
                </a>

                <button type="submit"
                    class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Update Pegawai
                </button>

            </div>

        </form>

    </x-common.component-card>

</div>

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('formPegawaiEdit');
        if (!form) return;

        const aturan = [
            { name: 'nama', pesan: 'Nama pegawai wajib diisi.' },
            { name: 'no_hp', pesan: 'Nomor HP wajib diisi.', pattern: /^[0-9+\-\s()]+$/, pesanPattern: 'Format nomor HP tidak valid.' },
            { name: 'username', pesan: 'Username wajib diisi.' },
            { name: 'email', pesan: 'Email wajib diisi.', email: true, pesanEmail: 'Format email tidak valid.' },
            { name: 'password', optional: true, minLen: 6, pesanMin: 'Password baru minimal 6 karakter.' },
            { name: 'jabatan', pesan: 'Jabatan wajib dipilih.' },
            { name: 'tanggal_masuk', pesan: 'Tanggal masuk wajib diisi.' },
            { name: 'alamat', pesan: 'Alamat wajib diisi.' },
        ];

        function setFieldError(name, message) {
            const el = form.querySelector(`[name="${name}"]`);
            const errEl = form.querySelector(`[data-error-for="${name}"]`);
            if (errEl) {
                errEl.textContent = message || '';
                errEl.classList.toggle('hidden', !message);
            }
            if (el) {
                el.classList.toggle('border-red-500', !!message);
                const trigger = el.closest('.daisy-select-wrap')?.querySelector('.daisy-select-trigger');
                if (trigger) trigger.classList.toggle('is-invalid', !!message);
            }
        }

        aturan.forEach(({ name }) => {
            const el = form.querySelector(`[name="${name}"]`);
            if (!el) return;
            const clear = () => setFieldError(name, '');
            el.addEventListener('input', clear);
            el.addEventListener('change', clear);
        });

        form.addEventListener('submit', function (e) {
            let pertamaGagal = null;

            aturan.forEach((r) => {
                const el = form.querySelector(`[name="${r.name}"]`);
                if (!el) return;
                const val = String(el.value || '').trim();
                let msg = '';

                if (!val && !r.optional) {
                    msg = r.pesan;
                } else if (val && r.minLen && val.length < r.minLen) {
                    msg = r.pesanMin;
                } else if (val && r.pattern && !r.pattern.test(val)) {
                    msg = r.pesanPattern;
                } else if (val && r.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                    msg = r.pesanEmail;
                }

                setFieldError(r.name, msg);
                if (msg && !pertamaGagal) {
                    pertamaGagal = el;
                }
            });

            if (pertamaGagal) {
                e.preventDefault();
                const target = pertamaGagal.closest('.daisy-select-wrap')?.querySelector('.daisy-select-trigger') || pertamaGagal;
                target.focus();
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    })();
</script>
@endpush

@endsection
