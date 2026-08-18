@extends('mobile.layout', ['title' => $bundling->nama_bundling])

@php
    $sedangUbah = (bool) $itemEdit;
@endphp

@section('content')

    <div x-data="{
                    jumlah: {{ $itemEdit['jumlah'] ?? 1 }},
                    catatan: @js($itemEdit['catatan'] ?? ''),
                    hargaSatuan: {{ (float) $bundling->harga }},
                    habis: {{ $habis ? 'true' : 'false' }},

                    get total() {
                        return this.hargaSatuan * this.jumlah;
                    },

                    tambahJumlah() {
                        if (this.jumlah < 50) {
                            this.jumlah++;
                        }
                    },

                    kurangiJumlah() {
                        if (this.jumlah > 1) {
                            this.jumlah--;
                        }
                    }
                }" class="relative">

        {{-- Foto bundling --}}
        <div class="relative -mx-5 -mt-6 aspect-square w-auto overflow-hidden bg-gray-50 sm:-mx-8 md:-mx-10">

            @if ($bundling->gambar)
                <img src="{{ asset('storage/' . $bundling->gambar) }}" alt="{{ $bundling->nama_bundling }}"
                    class="h-full w-full object-cover"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            @endif

            <div class="{{ $bundling->gambar ? 'hidden' : 'flex' }} h-full w-full items-center justify-center text-brand-200">
                @include('mobile.components.icon-cup', ['class' => 'h-20 w-20'])
            </div>

            <a href="{{ $sedangUbah ? route('member.checkout.index') : route('member.menu') }}"
                onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
                class="absolute left-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-700 shadow-md transition active:scale-95 active:bg-gray-50"
                aria-label="Kembali">
                <span class="rotate-180">
                    @include('mobile.components.icon-chevron-right', ['class' => 'h-5 w-5'])
                </span>
            </a>

            <span class="absolute left-4 top-16 rounded-full bg-orange-500 px-3 py-1 text-[11px] font-bold text-white shadow-sm">
                Bundling
            </span>

            @if ($habis)
                <span class="absolute left-4 bottom-4 rounded-full bg-gray-900/80 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-white">
                    Perlu Diproduksi
                </span>
            @endif

        </div>

        <div class="mt-5">

            <div>
                <p class="text-[11px] font-bold uppercase tracking-wide text-brand-700">
                    Paket Bundling
                </p>

                <h2 class="mt-1 text-lg font-extrabold leading-snug text-gray-900">
                    {{ $bundling->nama_bundling }}
                </h2>
                
                <div class="mt-4">
                    <p class="mt-1.5 text-xs leading-relaxed text-gray-500">
                        {{ $bundling->deskripsi ?: '' }}
                    </p>
                </div>

                <div class="mt-2 flex items-center gap-2">
                    <span class="text-base font-extrabold text-gray-900">
                        Rp {{ number_format($bundling->harga, 0, ',', '.') }}
                    </span>
                </div>
            </div>


            {{-- Isi paket --}}
            <div class="mt-4">
                <h3 class="text-xs font-bold text-gray-900">
                    Isi Paket
                </h3>

                <div class="mt-2 divide-y divide-gray-100 rounded-2xl border border-gray-100">
                    @foreach ($bundling->items as $bundlingItem)
                        <div class="flex items-center justify-between p-3">
                            <p class="text-xs font-semibold text-gray-800">{{ $bundlingItem->produk->nama_produk ?? 'Produk tidak ditemukan' }}</p>
                            <span class="shrink-0 text-xs font-bold text-gray-500">x{{ $bundlingItem->qty }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($habis)
                <div class="mt-4 flex items-start gap-2 rounded-2xl bg-amber-50 px-4 py-3 text-amber-800">
                    @include('mobile.components.icon-clock', ['class' => 'h-4 w-4 shrink-0 mt-0.5'])
                    <p class="text-[11px] font-semibold leading-relaxed">
                        Salah satu isi paket ini stoknya sedang kurang/habis, pesanan tetap bisa dibuat & akan
                        diproduksi dulu di dapur — waktu tunggu bisa lebih lama dari biasanya.
                    </p>
                </div>
            @endif

            <div class="mt-5">
                <h3 class="text-xs font-bold text-gray-900">
                    Catatan
                    <span class="font-medium text-gray-400">
                        (opsional)
                    </span>
                </h3>

                <textarea x-model="catatan" rows="2" maxlength="255" placeholder="Contoh: kurangi manis, dll."
                    class="mt-2 w-full resize-none rounded-2xl border border-gray-200 p-3 text-xs text-gray-700 placeholder:text-gray-400 focus:border-brand-700 focus:outline-none focus:ring-1 focus:ring-brand-700"></textarea>
            </div>

            <div class="h-32"></div>

        </div>

        <div class="fixed inset-x-0 bottom-0 z-50 mx-auto w-full border-t border-gray-100 bg-white px-5 pt-3 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] sm:px-8 md:px-10 2xl:max-w-6xl"
            style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">

            @if (Auth::guard('member')->check())

                @if ($sedangUbah)

                    <form method="POST" action="{{ route('member.keranjang.perbarui-bundling', $itemEdit['id']) }}">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="bundling_id" value="{{ $bundling->id }}">
                        <input type="hidden" name="jumlah" :value="jumlah">
                        <input type="hidden" name="catatan" :value="catatan">

                        <div class="flex items-center gap-3">

                            <div class="flex shrink-0 items-center gap-1.5 rounded-full bg-white">
                                <button type="button" @click="kurangiJumlah()"
                                    class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800 transition active:scale-90"
                                    aria-label="Kurangi jumlah">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                    </svg>
                                </button>

                                <span class="flex w-7 items-center justify-center text-base font-bold text-gray-900"
                                    x-text="jumlah"></span>

                                <button type="button" @click="tambahJumlah()"
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm transition active:scale-90"
                                    aria-label="Tambah jumlah">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>

                            <button type="submit"
                                class="flex min-w-0 flex-1 items-center justify-center rounded-full bg-brand-800 px-6 py-4 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]"
                                x-text="'Update – Rp ' + total.toLocaleString('id-ID')">
                            </button>

                        </div>

                    </form>

                @else

                <form method="POST" action="{{ route('member.keranjang.tambah-bundling', $bundling) }}"
                    onsubmit="event.preventDefault(); kirimTambahKeranjang(this);">
                    @csrf

                    <input type="hidden" name="jumlah" :value="jumlah">
                    <input type="hidden" name="catatan" :value="catatan">

                    <div class="flex items-center gap-3">

                        <div class="flex shrink-0 items-center gap-1.5 rounded-full bg-white">
                            <button type="button" @click="kurangiJumlah()"
                                class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-brand-800 text-brand-800 transition active:scale-90"
                                aria-label="Kurangi jumlah">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12h14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                                </svg>
                            </button>

                            <span class="flex w-7 items-center justify-center text-base font-bold text-gray-900"
                                x-text="jumlah"></span>

                            <button type="button" @click="tambahJumlah()"
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-800 text-white shadow-sm transition active:scale-90"
                                aria-label="Tambah jumlah">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" />
                                </svg>
                            </button>
                        </div>

                        <button type="submit"
                            class="flex min-w-0 flex-1 items-center justify-between rounded-full bg-brand-800 px-6 py-4 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">
                            <span>Add</span>
                            <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                        </button>

                    </div>

                </form>

                @endif

            @else

                <button type="button" onclick="bukaModalLoginRequired('Pesan')"
                    class="flex w-full items-center justify-between rounded-2xl bg-brand-800 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition active:scale-[0.99]">
                    <span>Login untuk Pesan</span>
                    <span x-text="'Rp ' + total.toLocaleString('id-ID')"></span>
                </button>

            @endif

        </div>

    </div>

@endsection