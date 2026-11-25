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
                class="flex h-8 w-8 items-center justify-center rounded-full border border-[#3C2415]/20 bg-white text-[#3A221B] shadow-xs hover:bg-[#FAF5E8] active:scale-95 transition">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>
            <div class="flex items-center gap-1.5">
                <h1 class="text-base font-extrabold text-[#3A221B]">My Account</h1>
            </div>
            <span class="w-8"></span>
        </div>

        @error('foto')
            <p class="mt-3 px-5 text-center text-xs font-semibold text-red-600">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('member.profil.update') }}" enctype="multipart/form-data"
            class="mt-5 flex flex-1 flex-col overflow-hidden" x-data="profilFotoCrop()">
            @csrf

            <div class="flex-none">
                <div class="relative bg-[#FAF4ED] border-b border-[#3C2415]/10 px-5 pb-16 pt-4">
                    {{-- Strawberry Watermarks in Top Banner (clipped safely inside banner) --}}
                    <div class="pointer-events-none absolute inset-0 overflow-hidden">
                        <span class="absolute -right-3 -top-3 h-24 w-24 opacity-20 select-none rotate-12">
                            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-full w-full object-contain">
                        </span>
                        <span class="absolute -left-3 -bottom-2 h-20 w-20 opacity-15 select-none -rotate-12">
                            <img src="{{ asset('images/decor/strawberry-2.svg') }}" alt="" class="h-full w-full object-contain">
                        </span>
                    </div>

                    <div class="relative z-10 flex justify-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#3C2415]/10 bg-white/90 px-3.5 py-1 text-xs font-semibold text-[#706864] shadow-xs">
                            <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="h-3.5 w-3.5 object-contain">
                            <span>Member since: {{ $member->created_at->translatedFormat('d F Y') }}</span>
                        </span>
                    </div>

                    <div class="absolute left-1/2 top-full h-24 w-24 -translate-x-1/2 -translate-y-1/2 z-20">
                        <div class="relative h-full w-full">
                            <span class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-gray-200 ring-4 ring-white shadow-md">
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
                            {{-- Pencil icon moved down (-bottom-2.5) so it doesn't collide/overlap over the face/pfp --}}
                            <label for="foto-trigger-mob"
                                class="absolute -bottom-2.5 left-1/2 flex h-8 w-8 -translate-x-1/2 cursor-pointer items-center justify-center rounded-full border-2 border-[#3A221B] bg-white text-[#3A221B] shadow-md hover:bg-[#FAF5E8] active:scale-95 transition z-30">
                                <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 pointer-events-none">
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
                <div class="h-14"></div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <div class="px-5">
                    @php $kelengkapan = $member->kelengkapan_profil; @endphp
                    <div class="px-0.5 pt-6 text-center">
                        <div class="flex items-center justify-between mb-1.5 px-0.5">
                            <span class="text-[11px] font-bold text-[#706864]">Profile Details</span>
                            <span class="text-[11px] font-bold text-[#3A221B]">{{ $kelengkapan }}%</span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-[#EADDCD]">
                            <div class="h-full rounded-full bg-[#5A3825] transition-all"
                                style="width: {{ $kelengkapan }}%"></div>
                        </div>
                    </div>

                    <div class="mt-7 grid grid-cols-1 md:grid-cols-2 gap-4 space-y-0">
                        <div>
                            <label for="nama-mob" class="text-xs font-semibold text-[#706864]">Full Name</label>
                            <input type="text" name="nama" id="nama-mob" value="{{ old('nama', $member->nama) }}"
                                class="mt-1.5 w-full rounded-xl border border-[#3C2415]/15 bg-white px-4 py-3 text-sm font-bold uppercase tracking-wide text-[#3A221B] outline-none transition focus:border-[#3A221B] focus:ring-2 focus:ring-[#3A221B]/10">
                            @error('nama')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="username-mob" class="text-xs font-semibold text-[#706864]">Username</label>
                            <input type="text" name="username" id="username-mob"
                                value="{{ old('username', $member->username) }}" placeholder="Enter your username"
                                oninput="this.value = this.value.replace(/\s/g, '').toLowerCase()"
                                class="mt-1.5 w-full rounded-xl border border-[#3C2415]/15 bg-white px-4 py-3 text-sm text-[#3A221B] outline-none transition placeholder:text-gray-400 focus:border-[#3A221B] focus:ring-2 focus:ring-[#3A221B]/10">
                            @error('username')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email-mob" class="text-xs font-semibold text-[#706864]">Email</label>
                            <input type="email" name="email" id="email-mob" value="{{ old('email', $member->email) }}"
                                placeholder="Enter your email address"
                                class="mt-1.5 w-full rounded-xl border border-[#3C2415]/15 bg-white px-4 py-3 text-sm text-[#3A221B] outline-none transition placeholder:text-gray-400 focus:border-[#3A221B] focus:ring-2 focus:ring-[#3A221B]/10">
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

                <div class="h-24"></div>
            </div>

            <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full max-w-none border-t border-[#3C2415]/10 bg-[#FAF5E8] p-3 sm:p-4 2xl:max-w-6xl"
                style="padding-bottom: max(env(safe-area-inset-bottom), 12px);">
                <button type="submit"
                    class="block w-full rounded-2xl bg-[#3A221B] hover:bg-[#4E2F26] active:bg-[#2C1811] py-3.5 text-sm font-bold uppercase tracking-wider text-[#FAF5E8] shadow-md transition">
                    Save Changes
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
                            <h3 class="font-extrabold text-gray-900 text-sm">Crop Profile Picture</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Adjust the photo area you wish to display</p>
                        </div>
                        <div class="bg-gray-100" style="max-height: 50vh; overflow: hidden;">
                            <img x-ref="cropImage" src="" alt="Crop" style="display: block; max-width: 100%; max-height: 50vh;">
                        </div>
                        <div class="p-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="batalCrop()"
                                class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 font-bold text-xs rounded-xl transition text-gray-700">
                                Cancel
                            </button>
                            <button type="button" @click="terapkanCrop()"
                                class="px-4 py-2.5 bg-[#3A221B] hover:bg-[#4E2F26] text-white font-bold text-xs rounded-xl transition shadow-sm">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </form>
    </div>


    {{-- ========================================== --}}
    {{-- DESKTOP VIEW (>= 1024px) - BESPOKE FRENCH BAKERY PROFILE HUB --}}
    {{-- ========================================== --}}
    <div id="profile-desktop-root" class="hidden lg:block w-full bg-[#FAF5E8] min-h-[calc(100vh-140px)] text-[#3C2415] py-10 px-6 sm:px-8 lg:px-12"
        x-data="{
            modalHapusAkunTerbuka: {{ isset($errors) && $errors->has('password') ? 'true' : 'false' }},
            menghapusAkun: false,
            passwordHapus: '',
            errorPassword: '{{ isset($errors) && $errors->has('password') ? $errors->first('password') : '' }}',
            lihatPassword: false,
            punyaPassword: {{ auth('member')->user() && empty(auth('member')->user()->google_id) && !is_null(auth('member')->user()->password) ? 'true' : 'false' }}
        }">
        <div class="max-w-5xl mx-auto space-y-8">

            {{-- Top Navigation & Metadata Bar --}}
            <div class="flex items-center justify-between border-b border-[#3C2415]/10 pb-5">
                <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                   class="inline-flex items-center gap-2 text-xs font-bold text-[#3C2415]/75 hover:text-[#8E1E26] transition">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Back to Account</span>
                </a>
            </div>

            {{-- Clean Page Header (NO HERO SECTION) --}}
            <div class="space-y-2">
                <h1 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-tight text-[#3C2415]">
                    Edit Profile
                </h1>
                <p class="text-sm text-[#3C2415]/75 max-w-2xl leading-relaxed">
                    Update your public member profile, avatar photo, and contact details for fresh order updates and bakery rewards.
                </p>
            </div>

            @error('foto')
                <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-xs font-bold text-red-600">
                    {{ $message }}
                </div>
            @enderror

            {{-- Two-Column Layout --}}
            <form method="POST" action="{{ route('member.profil.update') }}" enctype="multipart/form-data"
                  class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start" x-data="profilFotoCrop()">
                @csrf
                @php $kelengkapan = $member->kelengkapan_profil; @endphp

                {{-- Left Column: Avatar Showcase & Profile Completion --}}
                <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
                    {{-- Avatar Card --}}
                    <div class="rounded-[28px] bg-[#F5ECE3] p-6 border border-[#3C2415]/15 shadow-sm text-center space-y-4">
                        <div class="relative mx-auto w-28 h-28">
                            <span class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-white shadow-md">
                                <img x-show="fotoPreview" x-cloak :src="fotoPreview" alt="Preview"
                                     class="h-full w-full object-cover">
                                <span x-show="!fotoPreview" class="flex h-full w-full items-center justify-center">
                                    @if ($member->foto)
                                        <img src="{{ asset('storage/' . $member->foto) }}" alt="{{ $member->nama }}"
                                             class="h-full w-full object-cover">
                                    @else
                                        <span class="flex h-full w-full items-center justify-center bg-[#3C2415] text-[#FAF5E8]">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-12 w-12 text-[#FAF5E8]">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"
                                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    @endif
                                </span>
                            </span>

                            <label for="foto-trigger-desk"
                                   class="absolute bottom-0 right-0 flex h-9 w-9 cursor-pointer items-center justify-center rounded-full border-2 border-white bg-[#8E1E26] text-white hover:bg-[#3C2415] transition active:scale-95 shadow-md"
                                   title="Upload New Photo">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 pointer-events-none">
                                    <path d="m16.5 3.5 4 4L8 20l-4.5 1L4.5 16.5 16.5 3.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                </svg>
                                <input type="file" id="foto-trigger-desk" accept="image/*"
                                       class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                       x-ref="fotoTrigger"
                                       @change="pilihFoto($event)">
                            </label>
                            <input type="file" name="foto" x-ref="fotoInput" class="hidden">
                        </div>

                        <div class="space-y-1">
                            <h3 class="font-display font-black text-lg text-[#3C2415] truncate">{{ $member->nama }}</h3>
                            <p class="text-xs text-[#8E1E26] font-bold">★ {{ $member->level ?? 'Club Member' }}</p>
                            <div class="pt-1">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 border border-[#3C2415]/10 text-xs font-mono font-bold text-[#3C2415]">
                                    {{ $member->kode_member }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-[#3C2415]/10 text-[11px] text-[#3C2415]/60">
                            Member since {{ $member->created_at->format('d F Y') }}
                        </div>
                    </div>
                </div>

                {{-- Right Column: Form Inputs & Danger Zone --}}
                <div class="lg:col-span-8 space-y-6">
                    <div class="rounded-[28px] bg-[#F5ECE3] p-8 border border-[#3C2415]/15 shadow-sm space-y-6">
                        <div class="border-b border-[#3C2415]/10 pb-4">
                            <h2 class="font-display font-bold text-lg text-[#3C2415] uppercase tracking-tight">Account Information</h2>
                            <p class="text-xs text-[#3C2415]/70">All modifications are synced across all your connected devices.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nama-desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/75 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama" id="nama-desk" value="{{ old('nama', $member->nama) }}" required
                                       class="w-full rounded-2xl border border-[#3C2415]/20 bg-white px-4 py-3 text-sm font-bold uppercase tracking-wide text-[#3C2415] outline-none transition focus:border-[#8E1E26] focus:ring-4 focus:ring-[#8E1E26]/10">
                                @error('nama')
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="username-desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/75 mb-2">
                                    Username
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-sm font-bold text-[#3C2415]/40">@</span>
                                    <input type="text" name="username" id="username-desk"
                                           value="{{ old('username', $member->username) }}" placeholder="dailybaker"
                                           oninput="this.value = this.value.replace(/\s/g, '').toLowerCase()"
                                           class="w-full rounded-2xl border border-[#3C2415]/20 bg-white pl-8 pr-4 py-3 text-sm font-medium text-[#3C2415] outline-none transition placeholder:text-[#3C2415]/30 focus:border-[#8E1E26] focus:ring-4 focus:ring-[#8E1E26]/10">
                                </div>
                                @error('username')
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email-desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/75 mb-2">
                                    Email Address
                                </label>
                                <input type="email" name="email" id="email-desk" value="{{ old('email', $member->email) }}"
                                       placeholder="baker@daisybakery.com"
                                       class="w-full rounded-2xl border border-[#3C2415]/20 bg-white px-4 py-3 text-sm font-medium text-[#3C2415] outline-none transition placeholder:text-[#3C2415]/30 focus:border-[#8E1E26] focus:ring-4 focus:ring-[#8E1E26]/10">
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="no_hp-desk" class="block text-xs font-bold uppercase tracking-wider text-[#3C2415]/50">Phone Number</label>
                                    <span class="text-[10px] text-[#8E1E26] font-bold">WhatsApp Verified</span>
                                </div>
                                <div class="relative">
                                    <input type="text" id="no_hp-desk" value="{{ $member->no_hp }}" disabled
                                           class="w-full cursor-not-allowed rounded-2xl border border-[#3C2415]/10 bg-white/50 px-4 py-3 text-sm font-semibold text-[#3C2415]/60">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-[#3C2415]/10 flex items-center justify-between">
                            <span class="text-xs text-[#3C2415]/60">Review your information before saving.</span>
                            <button type="submit"
                                    class="rounded-xl bg-[#3C2415] hover:bg-[#8E1E26] px-8 py-3 text-xs font-extrabold uppercase tracking-wider text-[#FAF5E8] shadow-md transition active:scale-95 cursor-pointer">
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Crop Modal (teleport to body) --}}
                <template x-teleport="body">
                    <div x-show="showCropModal" x-cloak
                         class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                         x-transition.opacity.duration.200ms>
                        <div @click.away="batalCrop()"
                             class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                            <div class="p-6 border-b border-gray-100">
                                <h3 class="font-extrabold text-gray-900 text-sm">Crop Profile Photo</h3>
                                <p class="text-xs text-gray-500 mt-1">Adjust the photo area you want to display on your member card</p>
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
        </div>
    </div>

@endsection
