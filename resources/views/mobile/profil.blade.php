@extends('mobile.layout', ['title' => 'My Account'])

@section('content')

{{-- Cropper.js CDN --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<script>
    function profilFotoCrop() {
        return {
            fotoPreview: null,
            showCropModal: false,
            cropperInstance: null,

            pilihFoto(event) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.showCropModal = true;
                    this.$nextTick(() => {
                        const img = this.$refs.cropImage;
                        img.src = e.target.result;
                        setTimeout(() => {
                            if (this.cropperInstance) this.cropperInstance.destroy();
                            this.cropperInstance = new Cropper(img, {
                                aspectRatio: 1,
                                viewMode: 1,
                                autoCropArea: 1,
                                responsive: true,
                                background: false,
                            });
                        }, 150);
                    });
                };
                reader.readAsDataURL(file);
            },

            terapkanCrop() {
                if (!this.cropperInstance) return;
                this.cropperInstance.getCroppedCanvas({ maxWidth: 800, maxHeight: 800 }).toBlob((blob) => {
                    if (!blob) return;
                    const file = new File([blob], 'foto-profil.jpg', { type: 'image/jpeg' });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs.fotoInput.files = dt.files;
                    this.fotoPreview = URL.createObjectURL(blob);
                    this.showCropModal = false;
                    this.cropperInstance.destroy();
                    this.cropperInstance = null;
                }, 'image/jpeg', 0.92);
            },

            batalCrop() {
                if (this.cropperInstance) {
                    this.cropperInstance.destroy();
                    this.cropperInstance = null;
                }
                this.$refs.fotoTrigger.value = '';
                this.showCropModal = false;
            }
        };
    }
</script>

    {{-- ========================================== --}}
    {{-- MOBILE VIEW (< 1024px) - 100% UNTOUCHED ORIGINAL --}}
    {{-- ========================================== --}}
    <div x-data="{
        modalHapusAkunTerbuka: {{ isset($errors) && $errors->has('password') ? 'true' : 'false' }},
        menghapusAkun: false,
        passwordHapus: '',
        errorPassword: '{{ isset($errors) && $errors->has('password') ? $errors->first('password') : '' }}',
        lihatPassword: false,
        punyaPassword: {{ auth('member')->user() && empty(auth('member')->user()->google_id) && !is_null(auth('member')->user()->password) ? 'true' : 'false' }}
    }" class="flex h-screen max-h-screen flex-col overflow-hidden lg:hidden">

        <div class="-mt-6 flex flex-none items-center justify-between px-5 pt-6">
            <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>
            <h1 class="text-base font-extrabold text-brand-800">My Account</h1>
            <span class="w-8"></span>
        </div>

        @error('foto')
            <p class="mt-3 px-5 text-center text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('member.profil.update') }}" enctype="multipart/form-data"
            class="mt-5 flex flex-1 flex-col overflow-hidden" x-data="profilFotoCrop()">
            @csrf

            <div class="flex-none">
                <div class="relative bg-gray-50 px-5 pb-16 pt-4">
                    <div class="flex justify-center">
                        <span class="rounded-full bg-gray-100 px-4 py-1.5 text-xs font-medium text-gray-600">
                            Member since: {{ $member->created_at->translatedFormat('d F Y') }}
                        </span>
                    </div>

                    <div class="absolute left-1/2 top-full h-24 w-24 -translate-x-1/2 -translate-y-1/2">
                        <div class="relative h-full w-full">
                            <span class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-gray-200 ring-4 ring-white">
                                {{-- Preview foto hasil crop --}}
                                <img x-show="fotoPreview" x-cloak :src="fotoPreview" alt="Preview"
                                    class="h-full w-full object-cover">
                                {{-- Foto asli / placeholder --}}
                                <span x-show="!fotoPreview" class="flex h-full w-full items-center justify-center">
                                    @if ($member->foto)
                                        <img src="{{ asset('storage/' . $member->foto) }}" alt="{{ $member->nama }}"
                                            class="h-full w-full object-cover">
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-gray-400">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    @endif
                                </span>
                            </span>
                            <label for="foto-trigger-mob"
                                class="absolute -bottom-1 left-1/2 flex h-9 w-9 -translate-x-1/2 cursor-pointer items-center justify-center rounded-full border-2 border-brand-800 bg-white text-brand-800 active:scale-95">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 pointer-events-none">
                                    <path d="m16.5 3.5 4 4L8 20l-4.5 1L4.5 16.5 16.5 3.5Z" stroke="currentColor"
                                        stroke-width="1.8" stroke-linejoin="round" />
                                </svg>
                                <input type="file" id="foto-trigger-mob" accept="image/*"
                                    class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                    x-ref="fotoTrigger"
                                    @change="pilihFoto($event)">
                            </label>
                            {{-- Hidden file input untuk menyimpan hasil crop --}}
                            <input type="file" name="foto" x-ref="fotoInput" class="hidden">
                        </div>
                    </div>
                </div>
                <div class="h-12"></div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div class="px-5">
                    @php $kelengkapan = $member->kelengkapan_profil; @endphp
                    <div class="px-0.5 pt-6 text-center">
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-brand-800 transition-all"
                                style="width: {{ $kelengkapan }}%"></div>
                        </div>
                    </div>

                    <div class="mt-7 grid grid-cols-1 md:grid-cols-2 gap-4 space-y-0">
                        <div>
                            <label for="nama-mob" class="text-xs font-semibold text-gray-500">Nama</label>
                            <input type="text" name="nama" id="nama-mob" value="{{ old('nama', $member->nama) }}"
                                class="mt-1.5 w-full rounded-xl border border-brand-100 bg-brand-25 px-4 py-3 text-sm font-bold uppercase tracking-wide text-brand-800 outline-none transition focus:border-brand-800 focus:ring-2 focus:ring-brand-800/10">
                            @error('nama')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="username-mob" class="text-xs font-semibold text-gray-500">Username</label>
                            <input type="text" name="username" id="username-mob"
                                value="{{ old('username', $member->username) }}" placeholder="Buat username kamu"
                                oninput="this.value = this.value.replace(/\s/g, '').toLowerCase()"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                            @error('username')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email-mob" class="text-xs font-semibold text-gray-500">Email</label>
                            <input type="email" name="email" id="email-mob" value="{{ old('email', $member->email) }}"
                                placeholder="Masukkan email kamu"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                            @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="no_hp-mob" class="text-xs font-semibold text-gray-400">Phone Number</label>
                            <input type="text" id="no_hp-mob" value="{{ $member->no_hp }}" disabled
                                class="mt-1.5 w-full cursor-not-allowed rounded-xl border border-gray-100 bg-gray-100 px-4 py-3 text-sm text-gray-400">
                        </div>
                    </div>
                </div>

                <div class="mt-8 bg-gray-50 px-5 py-3">
                    <span class="text-xs font-bold tracking-wide text-gray-500">OTHERS</span>
                </div>

                <div class="px-5">
                    <button type="button"
                        @click.stop="modalHapusAkunTerbuka = true"
                        class="mt-5 flex items-center gap-2 text-sm font-bold text-red-700">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <path
                                d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-1 13a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L6 7h12Z"
                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        DELETE ACCOUNT
                    </button>
                </div>

                <div class="h-24"></div>
            </div>

            <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-none border-t border-gray-100 bg-white 2xl:max-w-6xl"
                style="padding-bottom: env(safe-area-inset-bottom);">
                <button type="submit"
                    class="block w-full bg-brand-800 py-4 text-sm font-bold uppercase tracking-wide text-white">
                    Save
                </button>
            </div>

            {{-- Crop Modal (teleport ke body supaya tidak terpotong overflow-hidden) --}}
            <template x-teleport="body">
                <div x-show="showCropModal" x-cloak
                    class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                    x-transition.opacity.duration.200ms>
                    <div @click.away="batalCrop()"
                        class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="font-extrabold text-gray-900 text-sm">Crop Foto Profil</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Atur area foto yang ingin ditampilkan</p>
                        </div>
                        <div class="bg-gray-100" style="max-height: 50vh; overflow: hidden;">
                            <img x-ref="cropImage" src="" alt="Crop" style="display: block; max-width: 100%; max-height: 50vh;">
                        </div>
                        <div class="p-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="batalCrop()"
                                class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 font-bold text-xs rounded-xl transition text-gray-700">
                                Batal
                            </button>
                            <button type="button" @click="terapkanCrop()"
                                class="px-4 py-2.5 bg-brand-800 hover:bg-brand-900 text-white font-bold text-xs rounded-xl transition shadow-sm">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </form>

        <div x-show="modalHapusAkunTerbuka" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="if (!menghapusAkun) { modalHapusAkunTerbuka = false; passwordHapus = ''; errorPassword = ''; }"
                class="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-gray-100">
                <form method="POST" action="{{ route('member.profil.hapus-akun') }}"
                    @submit="if (punyaPassword && passwordHapus.trim() === '') { errorPassword = 'Masukkan password kamu untuk konfirmasi.'; $event.preventDefault(); return; } menghapusAkun = true;">
                    @csrf
                    @method('DELETE')
                    <div class="p-6 text-center">
                        <div class="w-14 h-14 bg-red-100 text-red-600 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                            ⚠
                        </div>
                        <h3 class="font-extrabold text-gray-900 text-base">Hapus Akun?</h3>
                        <p class="text-xs text-gray-500 mt-2">
                            Yakin ingin menghapus akun kamu? Tindakan ini tidak bisa dibatalkan.
                        </p>
                        <template x-if="punyaPassword">
                            <div class="mt-4 text-left">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Konfirmasi Password</label>
                                <div class="relative">
                                    <input x-model="passwordHapus"
                                        name="password"
                                        :type="lihatPassword ? 'text' : 'password'"
                                        placeholder="Masukkan password kamu"
                                        :class="errorPassword ? 'border-red-400 focus:ring-red-400/20' : 'border-gray-200 focus:ring-brand-800/10'"
                                        class="w-full rounded-xl border bg-gray-50 px-4 py-2.5 pr-10 text-sm text-gray-800 outline-none transition focus:border-brand-800 focus:ring-2">
                                    <button type="button" @click="lihatPassword = !lihatPassword"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition">
                                        <svg x-show="!lihatPassword" viewBox="0 0 24 24" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg x-show="lihatPassword" x-cloak viewBox="0 0 24 24" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                                <p x-show="errorPassword" x-text="errorPassword" class="mt-1 text-xs text-red-600 font-semibold"></p>
                            </div>
                        </template>
                    </div>
                    <div class="bg-gray-50 p-4 border-t border-gray-100 flex justify-center gap-3">
                        <button type="button" @click="modalHapusAkunTerbuka = false; passwordHapus = ''; errorPassword = '';" :disabled="menghapusAkun"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 font-bold text-xs rounded-lg transition text-gray-700 disabled:opacity-50">Batal</button>
                        <button type="submit" :disabled="menghapusAkun"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition shadow-xs disabled:opacity-50">
                            <span x-show="!menghapusAkun">Hapus</span>
                            <span x-show="menghapusAkun" x-cloak>Menghapus...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - FRENCH BAKERY PROFILE HUB --}}
    {{-- ========================================== --}}
    <div x-data="{
        modalHapusAkunTerbuka: {{ isset($errors) && $errors->has('password') ? 'true' : 'false' }},
        menghapusAkun: false,
        passwordHapus: '',
        errorPassword: '{{ isset($errors) && $errors->has('password') ? $errors->first('password') : '' }}',
        lihatPassword: false,
        punyaPassword: {{ auth('member')->user() && empty(auth('member')->user()->google_id) && !is_null(auth('member')->user()->password) ? 'true' : 'false' }}
    }"
        class="hidden lg:block lg:max-w-4xl lg:mx-auto lg:my-10 bg-[#F5ECE3] rounded-[32px] border border-[#3C2415]/15 shadow-sm overflow-hidden text-[#3C2415]">

        {{-- Desktop-Only Card Header --}}
        <div class="flex items-center justify-between px-8 py-5 border-b border-[#3C2415]/10 bg-white/50">
            <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="inline-flex items-center gap-2 text-xs font-extrabold text-[#3C2415]/75 hover:text-[#8E1E26] transition">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to Account
            </a>
            <h1 class="text-xs font-extrabold uppercase tracking-widest text-[#8E1E26]">Edit Profile & Personal Info</h1>
        </div>

        @error('foto')
            <p class="mt-3 px-8 text-center text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('member.profil.update') }}" enctype="multipart/form-data"
            class="flex flex-col" x-data="profilFotoCrop()">
            @csrf

            @php $kelengkapan = $member->kelengkapan_profil; @endphp

            {{-- Desktop Layout Header (>=1024px) --}}
            <div class="flex items-center gap-6 px-8 py-6 bg-gradient-to-b from-white/40 to-transparent border-b border-[#3C2415]/10">
                <div class="relative shrink-0">
                    <span class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-3 border-[#3C2415]/20 bg-white shadow-md">
                        {{-- Preview foto hasil crop --}}
                        <img x-show="fotoPreview" x-cloak :src="fotoPreview" alt="Preview"
                            class="h-full w-full object-cover">
                        {{-- Foto asli / placeholder --}}
                        <span x-show="!fotoPreview" class="flex h-full w-full items-center justify-center">
                            @if ($member->foto)
                                <img src="{{ asset('storage/' . $member->foto) }}" alt="{{ $member->nama }}"
                                    class="h-full w-full object-cover">
                            @else
                                <svg viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-[#3C2415]/30">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @endif
                        </span>
                    </span>
                    <label for="foto-trigger-desk"
                        class="absolute bottom-0 right-0 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border-2 border-[#3C2415] bg-[#3C2415] text-[#FAF5E8] hover:bg-[#8E1E26] hover:border-[#8E1E26] transition active:scale-95 shadow-sm"
                        title="Change Profile Photo">
                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 pointer-events-none">
                            <path d="m16.5 3.5 4 4L8 20l-4.5 1L4.5 16.5 16.5 3.5Z" stroke="currentColor"
                                stroke-width="2" stroke-linejoin="round" />
                        </svg>
                        <input type="file" id="foto-trigger-desk" accept="image/*"
                            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                            x-ref="fotoTrigger"
                            @change="pilihFoto($event)">
                    </label>
                    {{-- Hidden file input untuk menyimpan hasil crop --}}
                    <input type="file" name="foto" x-ref="fotoInput" class="hidden">
                </div>

                <div class="flex-1 space-y-1.5">
                    <div class="flex items-center gap-3">
                        <h2 class="font-display font-black text-xl text-[#3C2415]">{{ $member->nama }}</h2>
                        <span class="rounded-full bg-[#8E1E26]/10 border border-[#8E1E26]/20 px-3 py-0.5 text-[11px] font-bold text-[#8E1E26]">
                            Member since {{ $member->created_at->format('d F Y') }}
                        </span>
                    </div>

                    <div class="max-w-sm pt-1 space-y-1">
                        <div class="flex items-center justify-between text-xs font-semibold text-[#3C2415]/75">
                            <span>Profile Completion</span>
                            <span class="text-[#8E1E26] font-extrabold">{{ $kelengkapan }}%</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-[#3C2415]/10">
                            <div class="h-full rounded-full bg-[#8E1E26] transition-all duration-500"
                                style="width: {{ $kelengkapan }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Input Fields Grid --}}
            <div class="px-8 py-6 space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="nama-desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/75 mb-1.5">Full Name</label>
                        <input type="text" name="nama" id="nama-desk" value="{{ old('nama', $member->nama) }}"
                            class="w-full rounded-2xl border border-[#3C2415]/20 bg-white px-4 py-3 text-sm font-bold uppercase tracking-wide text-[#3C2415] outline-none transition focus:border-[#8E1E26] focus:ring-4 focus:ring-[#8E1E26]/10">
                        @error('nama')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="username-desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/75 mb-1.5">Username</label>
                        <input type="text" name="username" id="username-desk"
                            value="{{ old('username', $member->username) }}" placeholder="e.g. dailybaker"
                            oninput="this.value = this.value.replace(/\s/g, '').toLowerCase()"
                            class="w-full rounded-2xl border border-[#3C2415]/20 bg-white px-4 py-3 text-sm font-medium text-[#3C2415] outline-none transition placeholder:text-[#3C2415]/30 focus:border-[#8E1E26] focus:ring-4 focus:ring-[#8E1E26]/10">
                        @error('username')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email-desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/75 mb-1.5">Email Address</label>
                        <input type="email" name="email" id="email-desk" value="{{ old('email', $member->email) }}"
                            placeholder="e.g. baker@daisybakery.com"
                            class="w-full rounded-2xl border border-[#3C2415]/20 bg-white px-4 py-3 text-sm font-medium text-[#3C2415] outline-none transition placeholder:text-[#3C2415]/30 focus:border-[#8E1E26] focus:ring-4 focus:ring-[#8E1E26]/10">
                        @error('email')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="no_hp-desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/50">Phone Number</label>
                            <span class="text-[10px] text-[#3C2415]/50 font-medium">(Cannot be modified)</span>
                        </div>
                        <input type="text" id="no_hp-desk" value="{{ $member->no_hp }}" disabled
                            class="w-full cursor-not-allowed rounded-2xl border border-[#3C2415]/10 bg-white/40 px-4 py-3 text-sm font-semibold text-[#3C2415]/50">
                    </div>
                </div>

                {{-- Desktop Danger Zone Card (>=1024px) --}}
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-red-200 bg-red-50/60 p-5">
                    <div class="space-y-0.5">
                        <h4 class="text-xs font-extrabold text-red-900 uppercase tracking-wide">Danger Zone</h4>
                        <p class="text-xs text-red-700">Deleting your account will permanently remove your transaction history and reward points.</p>
                    </div>
                    <button type="button"
                        @click.stop="modalHapusAkunTerbuka = true"
                        class="shrink-0 rounded-xl bg-white px-4 py-2 text-xs font-extrabold text-red-700 border border-red-200 shadow-xs hover:bg-red-600 hover:text-white hover:border-red-600 transition active:scale-95 cursor-pointer">
                        Delete Account
                    </button>
                </div>
            </div>

            {{-- Desktop Save Button Bar (Inside Card Footer) --}}
            <div class="flex items-center justify-between border-t border-[#3C2415]/10 bg-white/50 px-8 py-4">
                <span class="text-xs text-[#3C2415]/60 font-medium">Please ensure all your personal details are accurate.</span>
                <button type="submit"
                    class="rounded-xl bg-[#3C2415] hover:bg-[#8E1E26] px-8 py-3 text-xs font-extrabold uppercase tracking-wider text-[#FAF5E8] shadow-md transition active:scale-95 cursor-pointer">
                    Save Changes
                </button>
            </div>

            {{-- Crop Modal (teleport ke body supaya tidak terpotong overflow-hidden) --}}
            <template x-teleport="body">
                <div x-show="showCropModal" x-cloak
                    class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                    x-transition.opacity.duration.200ms>
                    <div @click.away="batalCrop()"
                        class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                        <div class="p-5 border-b border-gray-100">
                            <h3 class="font-extrabold text-gray-900 text-sm">Crop Profile Photo</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Adjust the photo area you want to display</p>
                        </div>
                        <div class="bg-gray-100" style="max-height: 50vh; overflow: hidden;">
                            <img x-ref="cropImage" src="" alt="Crop" style="display: block; max-width: 100%; max-height: 50vh;">
                        </div>
                        <div class="p-5 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="batalCrop()"
                                class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 font-bold text-xs rounded-xl transition text-gray-700 cursor-pointer">
                                Cancel
                            </button>
                            <button type="button" @click="terapkanCrop()"
                                class="px-5 py-2.5 bg-[#3C2415] hover:bg-[#8E1E26] text-white font-bold text-xs rounded-xl transition shadow-sm cursor-pointer">
                                Apply Photo
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </form>

        <div x-show="modalHapusAkunTerbuka" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="if (!menghapusAkun) { modalHapusAkunTerbuka = false; passwordHapus = ''; errorPassword = ''; }"
                class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-gray-100">
                <form method="POST" action="{{ route('member.profil.hapus-akun') }}"
                    @submit="if (punyaPassword && passwordHapus.trim() === '') { errorPassword = 'Enter your password to confirm.'; $event.preventDefault(); return; } menghapusAkun = true;">
                    @csrf
                    @method('DELETE')
                    <div class="p-6 text-center">
                        <div class="w-14 h-14 bg-red-100 text-red-600 font-bold flex items-center justify-center rounded-full text-2xl mx-auto mb-4">
                            ⚠
                        </div>
                        <h3 class="font-extrabold text-gray-900 text-base">Delete Account?</h3>
                        <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                            Are you sure you want to delete your account? This action is permanent and cannot be undone.
                        </p>
                        <template x-if="punyaPassword">
                            <div class="mt-4 text-left">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Confirm Password</label>
                                <div class="relative">
                                    <input x-model="passwordHapus"
                                        name="password"
                                        :type="lihatPassword ? 'text' : 'password'"
                                        placeholder="Enter your password"
                                        :class="errorPassword ? 'border-red-400 focus:ring-red-400/20' : 'border-gray-200 focus:ring-brand-800/10'"
                                        class="w-full rounded-xl border bg-gray-50 px-4 py-2.5 pr-10 text-sm text-gray-800 outline-none transition focus:border-brand-800 focus:ring-2">
                                    <button type="button" @click="lihatPassword = !lihatPassword"
                                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition">
                                        <svg x-show="!lihatPassword" viewBox="0 0 24 24" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1  12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg x-show="lihatPassword" x-cloak viewBox="0 0 24 24" fill="none" class="h-4 w-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                                <p x-show="errorPassword" x-text="errorPassword" class="mt-1 text-xs text-red-600 font-semibold"></p>
                            </div>
                        </template>
                    </div>
                    <div class="bg-gray-50 p-4 border-t border-gray-100 flex justify-center gap-3">
                        <button type="button" @click="modalHapusAkunTerbuka = false; passwordHapus = ''; errorPassword = '';" :disabled="menghapusAkun"
                            class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 font-bold text-xs rounded-xl transition text-gray-700 disabled:opacity-50 cursor-pointer">Cancel</button>
                        <button type="submit" :disabled="menghapusAkun"
                            class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl transition shadow-xs disabled:opacity-50 cursor-pointer">
                            <span x-show="!menghapusAkun">Yes, Delete Account</span>
                            <span x-show="menghapusAkun" x-cloak>Deleting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection