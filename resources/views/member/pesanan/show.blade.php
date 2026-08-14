@extends('member.layout', ['title' => 'Status Pesanan'])

@php
    $lunas = $transaksi->status_pembayaran === 'Lunas';
    $steps = [
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        \App\Models\Transaksi::STATUS_DIPROSES => 'Diproses',
        \App\Models\Transaksi::STATUS_SIAP_DIAMBIL => 'Ready for Pickup',
        \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL => 'Sudah Diambil',
    ];
    $urutan = array_keys($steps);
    $statusSekarang = $lunas ? $transaksi->status_pesanan : 'menunggu_pembayaran';
    $dibatalkan = $transaksi->status_pesanan === \App\Models\Transaksi::STATUS_DIBATALKAN;
    $indexSekarang = array_search($statusSekarang, $urutan, true);
    $selesaiSemua = $lunas && $transaksi->status_pesanan === \App\Models\Transaksi::STATUS_SUDAH_DIAMBIL;
@endphp

@section('content')

    <div x-data="{}"
         x-init="{{ $dibatalkan || $selesaiSemua ? '' : "setInterval(() => { fetch('" . route('member.pesanan.status', $transaksi) . "').then(r => r.json()).then(d => { if (d.status_pesanan !== '{$transaksi->status_pesanan}' || d.status_pembayaran !== '{$transaksi->status_pembayaran}' || d.bukti_pembayaran !== " . json_encode($transaksi->bukti_pembayaran) . " || d.bukti_dikonfirmasi_pada !== " . json_encode(optional($transaksi->bukti_dikonfirmasi_pada)->toJSON()) . " || d.catatan_penolakan !== " . json_encode($transaksi->catatan_penolakan) . ") location.reload(); }); }, 8000)" }}">

        <div class="relative flex items-center justify-center">
            <a href="{{ route('member.riwayat') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
               class="absolute left-0 flex h-8 w-8 items-center justify-center rounded-full text-gray-500 transition active:bg-gray-50">
                <span class="rotate-180">@include('member.components.icon-chevron-right', ['class' => 'h-4 w-4'])</span>
            </a>
            <h1 class="text-sm font-bold text-gray-900">Status Pesanan</h1>
        </div>

        @if ($dibatalkan)
            <div class="mt-6 rounded-2xl border border-red-100 bg-red-50 p-4 text-center">
                <p class="text-xs font-bold text-red-600">Pesanan Dibatalkan</p>
                <p class="mt-1 text-[11px] text-red-400">{{ $transaksi->kode_transaksi }}</p>
            </div>
        @endif

        {{-- Progress status --}}
        @unless ($dibatalkan)
            <div class="mt-6 flex items-start">
                @foreach ($steps as $key => $label)
                    @php
                        $idx = array_search($key, $urutan, true);
                        $selesai = $indexSekarang !== false && $idx <= $indexSekarang;
                    @endphp
                    <div class="flex flex-1 flex-col items-center text-center">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-white {{ $selesai ? 'bg-brand-800' : 'bg-gray-200' }}">
                            @if ($selesai)
                                @include('member.components.icon-check-circle', ['class' => 'h-4 w-4'])
                            @else
                                <span class="h-2 w-2 rounded-full bg-white"></span>
                            @endif
                        </span>
                        <p class="mt-2 max-w-[80px] text-[10px] font-bold {{ $selesai ? 'text-brand-800' : 'text-gray-400' }}">{{ $label }}</p>
                    </div>
                    @if (! $loop->last)
                        <div class="mt-4 h-0.5 flex-1 {{ $idx < $indexSekarang ? 'bg-brand-800' : 'bg-gray-200' }}"></div>
                    @endif
                @endforeach
            </div>

            <p class="mt-4 text-center text-xs font-semibold text-gray-500">
                @if ($statusSekarang === 'menunggu_pembayaran')
                    Menunggu pembayaran kamu diverifikasi oleh toko.
                @elseif ($statusSekarang === \App\Models\Transaksi::STATUS_DIPROSES)
                    Pesanan kamu sedang disiapkan oleh toko.
                @elseif ($statusSekarang === \App\Models\Transaksi::STATUS_SIAP_DIAMBIL)
                    Pesanan sudah siap! Silakan ambil di kasir.
                @else
                    Pesanan sudah kamu ambil. Terima kasih!
                @endif
            </p>
        @endunless

        {{-- Detail pesanan --}}
        @php
            // Satu produk bisa punya lebih dari satu baris detail kalau
            // sebagian dipenuhi dari stok & sebagian lagi butuh diproduksi
            // (lihat CheckoutController::pecahJumlahProduksi()). Digabung
            // per nama produk supaya member cuma lihat satu baris ringkas.
            $detailRingkas = $transaksi->detailTransaksi
                ->filter(function ($item) {
                    // Buang baris komponen bundling (sudah diwakilkan oleh
                    // baris ringkasan bundling_id, komponen ini cuma buat
                    // stok/tiket dapur — sama seperti filter di struk pelanggan).
                    if (! is_null($item->bundling_id) && ! is_null($item->produk_id) && (float) $item->harga_satuan == 0 && (float) $item->subtotal == 0) {
                        return false;
                    }
                    return true;
                })
                ->groupBy('nama_produk')
                ->map(function ($group) {
                    $first = $group->first();
                    return (object) [
                        'nama_produk' => $first->nama_produk,
                        'jumlah' => $group->sum('jumlah'),
                        'subtotal' => $group->sum('subtotal'),
                        'catatan' => $group->pluck('catatan')->filter()->unique()->implode('; '),
                    ];
                })
                ->values();
        @endphp
        <div class="mt-6">
            <h2 class="text-sm font-bold text-gray-900">Detail Pesanan</h2>
            <div class="mt-2 divide-y divide-gray-100 rounded-2xl border border-gray-100">
                @foreach ($detailRingkas as $detail)
                    <div class="flex items-center justify-between p-3">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold text-gray-900">{{ $detail->jumlah }}x {{ $detail->nama_produk }}</p>
                            @if ($detail->catatan)
                                <p class="truncate text-[11px] text-gray-400">{{ $detail->catatan }}</p>
                            @endif
                        </div>
                        <p class="shrink-0 text-xs font-bold text-gray-700">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 space-y-1.5 rounded-2xl border border-dashed border-gray-200 p-4">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($transaksi->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($transaksi->diskon > 0)
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Diskon {{ $transaksi->diskon_label ? "({$transaksi->diskon_label})" : '' }}</span>
                        <span class="font-semibold text-red-500">- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between border-t border-dashed border-gray-200 pt-1.5 text-sm">
                    <span class="font-bold text-gray-900">Total</span>
                    <span class="font-extrabold text-gray-900">Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
                </div>
                <div class="!mt-2 flex justify-between text-[11px] text-gray-400">
                    <span>Status Pembayaran</span>
                    <span class="font-semibold {{ $transaksi->status_pembayaran === 'Lunas' ? 'text-green-600' : 'text-amber-600' }}">{{ $transaksi->status_pembayaran }}</span>
                </div>
            </div>
        </div>

        {{-- Bukti Pembayaran: pembayaran dilakukan member sendiri di luar
             aplikasi (transfer/QRIS manual), foto ini yang jadi bukti buat
             diverifikasi kasir sebelum status ditandai Lunas. --}}
        @if ($transaksi->status_pembayaran !== 'Lunas')
            @php $terkonfirmasi = ! is_null($transaksi->bukti_dikonfirmasi_pada); @endphp
            <div class="mt-4" x-data="{ mengupload: false, tampilSukses: {{ session('success') ? 'true' : 'false' }} }">
                <h2 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h2>

                @if (session('success'))
                    <p x-show="tampilSukses" x-init="setTimeout(() => tampilSukses = false, 4000)"
                       x-transition:leave="transition ease-in duration-300"
                       x-transition:leave-start="opacity-100 translate-y-0"
                       x-transition:leave-end="opacity-0 -translate-y-1"
                       class="mt-2 flex items-center gap-1.5 text-xs font-semibold text-green-600">
                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 shrink-0"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>{{ session('success') }}</span>
                    </p>
                @endif
                @if (session('error'))
                    <p class="mt-2 text-xs font-semibold text-red-600">{{ session('error') }}</p>
                @endif
                @error('bukti_pembayaran')
                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror

                {{-- Alasan penolakan: dikasih kasir waktu bukti sebelumnya ditolak.
                     Cuma tampil selama member belum upload ulang (bukti_pembayaran
                     null lagi), sama kayak logic di panel admin. --}}
                @if ($transaksi->catatan_penolakan && ! $transaksi->bukti_pembayaran)
                    <div class="mt-2 flex items-start gap-2 rounded-2xl border border-red-100 bg-red-50 p-3">
                        <svg viewBox="0 0 24 24" fill="none" class="mt-1 h-4 w-4 shrink-0 text-red-500"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L14.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <p class="text-[11px] text-red-600">
                            <span class="font-bold">Bukti pembayaran ditolak:</span>
                            {{ $transaksi->catatan_penolakan }} Silakan upload ulang bukti pembayaran yang benar.
                        </p>
                    </div>
                @endif

                @if ($terkonfirmasi)
                    <div class="mt-2 flex items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-2.5">
                        <button type="button" onclick="openSheet('modal-bukti-pembayaran')"
                                class="group relative h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-gray-200">
                            <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                                 class="h-full w-full object-cover">
                            <span class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-active:bg-black/30">
                                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-white opacity-0 transition group-active:opacity-100"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span class="absolute bottom-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-black/50 text-white">
                                <svg viewBox="0 0 24 24" fill="none" class="h-2.5 w-2.5"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </button>
                        <span class="text-[11px] font-semibold text-green-600">Bukti sudah dikonfirmasi, menunggu verifikasi toko.</span>
                    </div>

                    {{-- Modal lihat bukti pembayaran ukuran penuh --}}
                    <dialog id="modal-bukti-pembayaran" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0 sm:max-w-md md:max-w-lg">
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h3>
                                <button type="button" onclick="closeSheet('modal-bukti-pembayaran')"
                                        class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-50 hover:text-gray-600">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </div>
                            <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                                 class="mt-3 w-full rounded-2xl border border-gray-100 object-contain">
                        </div>
                    </dialog>
                @else
                    <form method="POST" action="{{ route('member.pesanan.upload-bukti', $transaksi) }}"
                          enctype="multipart/form-data" class="mt-2"
                          @submit="mengupload = true">
                        @csrf

                        <label for="bukti_pembayaran"
                               class="relative flex cursor-pointer items-center gap-3 overflow-hidden rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 p-2.5 text-left transition active:bg-gray-100">

                            @if ($transaksi->bukti_pembayaran)
                                <button type="button" onclick="event.preventDefault(); event.stopPropagation(); openSheet('modal-bukti-pembayaran')"
                                        class="group relative z-10 h-12 w-12 shrink-0 overflow-hidden rounded-xl">
                                    <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                                         class="h-full w-full object-cover">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-active:bg-black/30">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5 text-white opacity-0 transition group-active:opacity-100"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                    <span class="absolute bottom-0.5 right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-black/50 text-white">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-2.5 w-2.5"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                </button>
                                <span class="text-[11px] font-semibold text-brand-800">Tap untuk ganti bukti</span>
                            @else
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 16V4m0 0L7 9m5-5 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="flex flex-col items-start">
                                    <span class="text-xs font-bold text-gray-700">Upload bukti transfer / QRIS</span>
                                    <span class="text-[11px] text-gray-400">JPG atau PNG, maks 4MB</span>
                                </span>
                            @endif

                            <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/*"
                                   class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                   @change="if ($event.target.files.length) { $event.target.form.submit(); }">

                            <span x-show="mengupload" x-cloak
                                  class="absolute inset-0 flex items-center justify-center bg-white/80">
                                <svg class="h-5 w-5 animate-spin text-brand-800" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                </svg>
                            </span>
                        </label>
                    </form>

                    @if ($transaksi->bukti_pembayaran)
                        {{-- Modal lihat bukti pembayaran ukuran penuh --}}
                        <dialog id="modal-bukti-pembayaran" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0 sm:max-w-md md:max-w-lg">
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h3>
                                    <button type="button" onclick="closeSheet('modal-bukti-pembayaran')"
                                            class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-50 hover:text-gray-600">
                                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                                <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                                     class="mt-3 w-full rounded-2xl border border-gray-100 object-contain">
                            </div>
                        </dialog>

                        <form method="POST" action="{{ route('member.pesanan.konfirmasi-bukti', $transaksi) }}" class="mt-2">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-xl bg-brand-800 py-2.5 text-xs font-bold text-white transition active:bg-brand-900">
                                Konfirmasi Pembayaran
                            </button>
                        </form>
                    @endif
                @endif

                <p class="mt-2 text-[11px] text-gray-400">
                    Upload bukti agar kasir bisa verifikasi.
                    @if ($transaksi->bukti_pembayaran && ! $terkonfirmasi)
                        Setelah yakin fotonya benar, tap "Konfirmasi Pembayaran" — bukti tidak bisa diganti lagi setelah dikonfirmasi.
                    @endif
                </p>
            </div>
        @elseif ($transaksi->bukti_pembayaran)
            <div class="mt-4">
                <h2 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h2>

                <button type="button" onclick="openSheet('modal-bukti-pembayaran')"
                        class="group relative mt-2 h-20 w-20 overflow-hidden rounded-2xl border border-gray-100">
                    <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                         class="h-full w-full object-cover">
                    <span class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-active:bg-black/30">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-white opacity-0 transition group-active:opacity-100">
                            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="absolute bottom-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-black/50 text-white">
                        <svg viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>
            </div>

            {{-- Modal lihat bukti pembayaran ukuran penuh --}}
            <dialog id="modal-bukti-pembayaran" class="modal-center fixed inset-0 m-auto w-[calc(100%-2.5rem)] max-w-sm rounded-3xl p-0 shadow-2xl border-0 sm:max-w-md md:max-w-lg">
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900">Bukti Pembayaran</h3>
                        <button type="button" onclick="closeSheet('modal-bukti-pembayaran')"
                                class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-50 hover:text-gray-600">
                            <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                    <img src="{{ asset('storage/' . $transaksi->bukti_pembayaran) }}" alt="Bukti pembayaran"
                         class="mt-3 w-full rounded-2xl border border-gray-100 object-contain">
                </div>
            </dialog>
        @endif

    </div>

@endsection