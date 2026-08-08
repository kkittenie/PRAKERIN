@extends('member.layout', ['title' => 'My Account'])

@section('content')

    {{-- Fixed-height page wrapper: only the middle section (below) scrolls --}}
    <div class="flex h-screen max-h-screen flex-col overflow-hidden">

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

        <form method="POST" action="{{ route('member.profil.update') }}" enctype="multipart/form-data"
            class="mt-5 flex flex-1 flex-col overflow-hidden pb-6">
            @csrf

            {{-- Background abu: full-bleed otomatis, karena <main> di halaman ini sudah tanpa padding horizontal.
                Foto profil "menggantung" absolute, separuh di dalam abu, separuh di putih. --}}
                <div class="flex-none">
                    <div class="relative bg-gray-50 px-5 pb-16 pt-4">
                        <div class="flex justify-center">
                            <span class="rounded-full bg-gray-100 px-4 py-1.5 text-xs font-medium text-gray-600">
                                Member since: {{ $member->created_at->translatedFormat('d F Y') }}
                            </span>
                        </div>

                        {{-- top-full + -translate-y-1/2 = dipatok di garis bawah box, naik separuh tinggi sendiri
                        → otomatis selalu 50:50 nutup abu vs nongol putih, apapun padding box-nya --}}
                        <div class="absolute left-1/2 top-full h-24 w-24 -translate-x-1/2 -translate-y-1/2">
                            <div class="relative h-full w-full">
                                <span
                                    class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-gray-200 ring-4 ring-white">
                                    @if ($member->foto)
                                        <img src="{{ asset('storage/' . $member->foto) }}" alt="{{ $member->nama }}"
                                            class="h-full w-full object-cover">
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-gray-400">
                                            <path
                                                d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    @endif
                                </span>
                                <label for="foto"
                                    class="absolute -bottom-1 left-1/2 flex h-8 w-8 -translate-x-1/2 cursor-pointer items-center justify-center rounded-full border-2 border-brand-800 bg-white text-brand-800">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                        <path d="m16.5 3.5 4 4L8 20l-4.5 1L4.5 16.5 16.5 3.5Z" stroke="currentColor"
                                            stroke-width="1.8" stroke-linejoin="round" />
                                    </svg>
                                    <input type="file" name="foto" id="foto" accept="image/*" class="hidden"
                                        onchange="this.form.submit()">
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Spacer: foto absolute nggak makan ruang di flow, jadi kasih jarak manual
                    sebesar separuh tinggi foto (h-24=96px → separuhnya h-12=48px)
                    supaya konten di bawah nggak ketiban foto --}}
                    <div class="h-12"></div>
                </div>

                {{-- Scrollable section starts here: from "Complete your profile" down to the Save button --}}
                <div class="flex-1 overflow-y-auto">

                    {{-- Konten yang butuh inset kiri-kanan dibungkus px-5 di sini --}}
                    <div class="px-5">
                        @php $kelengkapan = $member->kelengkapan_profil; @endphp
                        <div class="px-0.5 pt-6 text-center">
                            <p class="text-sm font-bold text-brand-800">Complete your profile and earn vouchers on your
                                special day!</p>
                            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-brand-800 transition-all"
                                    style="width: {{ $kelengkapan }}%"></div>
                            </div>
                        </div>

                        <div class="mt-7 space-y-4">
                            <div>
                                <label for="nama" class="text-xs font-semibold text-gray-500">Username</label>
                                <input type="text" name="nama" id="nama" value="{{ old('nama', $member->nama) }}"
                                    class="mt-1.5 w-full rounded-xl border border-brand-100 bg-brand-25 px-4 py-3 text-sm font-bold uppercase tracking-wide text-brand-800 outline-none transition focus:border-brand-800 focus:ring-2 focus:ring-brand-800/10">
                                @error('nama')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="text-xs font-semibold text-gray-500">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $member->email) }}"
                                    placeholder="Masukkan email kamu"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                                @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="tanggal_lahir" class="text-xs font-semibold text-gray-500">Date of Birth</label>
                                <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                                    value="{{ old('tanggal_lahir', $member->tanggal_lahir?->format('Y-m-d')) }}"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-brand-800 focus:bg-white focus:ring-2 focus:ring-brand-800/10">
                                @error('tanggal_lahir')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="no_hp" class="text-xs font-semibold text-gray-400">Phone Number</label>
                                <input type="text" id="no_hp" value="{{ $member->no_hp }}" disabled
                                    class="mt-1.5 w-full cursor-not-allowed rounded-xl border border-gray-100 bg-gray-100 px-4 py-3 text-sm text-gray-400">
                            </div>
                        </div>
                    </div>

                    {{-- OTHERS bar: full-bleed, nggak dibungkus px-5 di luar, cuma di dalam buat teksnya --}}
                    <div class="mt-8 bg-gray-50 px-5 py-3">
                        <span class="text-xs font-bold tracking-wide text-gray-500">OTHERS</span>
                    </div>

                    <div class="px-5">
                        <button type="button"
                            onclick="return confirm('Yakin ingin menghapus akun kamu? Tindakan ini tidak bisa dibatalkan.')"
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

                    {{-- Save button: full-bleed, tombolnya sendiri yang nyampe ujung (bukan cuma wadahnya) --}}
                    <div class="mt-8">
                        <button type="submit"
                            class="w-full rounded-lg bg-brand-800 py-4 text-sm font-bold uppercase tracking-wide text-white">
                            Save
                        </button>
                    </div>

                </div>
                {{-- Scrollable section ends here --}}

        </form>

    </div>

@endsection