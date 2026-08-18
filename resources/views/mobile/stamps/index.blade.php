@extends('mobile.layout', ['title' => 'Stamp'])

@section('content')

    <h1 class="text-center text-lg font-extrabold text-gray-900">Vouchers</h1>

    <div class="mt-3 flex items-center justify-center gap-1.5 rounded-2xl border border-gray-100 bg-brand-25 py-2.5 text-xs text-gray-500">
        Poin kamu saat ini:
        <span class="font-bold text-brand-800">{{ number_format($member->poin, 0, ',', '.') }}</span>
    </div>

    {{-- Same tab bar as the voucher catalog, so Stamp reads as a tab, not a separate page --}}
    <div class="mt-5 grid grid-cols-3 border-b border-gray-100">
        @foreach ([
            ['label' => 'Semua', 'href' => route('member.reward.index'), 'active' => false],
            ['label' => 'Voucher', 'href' => route('member.reward.index', ['tab' => 'voucher']), 'active' => false],
            ['label' => 'Stamp', 'href' => route('member.stamps.index'), 'active' => true],
        ] as $tab)
            <a href="{{ $tab['href'] }}" onclick="event.preventDefault(); animateNavigate(this.href, 'forward');"
               class="relative pb-3 text-center text-[13px] font-bold transition {{ $tab['active'] ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                {{ $tab['label'] }}
                @if ($tab['active'])
                    <span class="absolute inset-x-0 -bottom-px mx-auto h-[2.5px] w-10 rounded-full bg-brand-800"></span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Everything below is fetched and injected as-is into the Stamp pane on the
         Vouchers page (see rewards/index.blade.php), so this stays a self-contained
         chunk that also works fine on its own when this page is opened directly. --}}
    <div id="stamp-tab-content" class="flex flex-1 flex-col">

    @if (! $program)

        @include('mobile.components.empty-state', [
            'title' => 'Belum Ada Program Stamp',
            'description' => 'Sabar ya, program kumpulin stamp bakal dibuka lagi nanti.',
            'icon' => 'icon-cake',
            'iconBg' => 'bg-orange-50',
            'iconColor' => 'text-orange-500',
        ])

    @else

        @php
            $target = max(1, $program->jumlah_stamp_dibutuhkan);
            $current = min($memberStamp->jumlah_stamp, $target);
            $penuh = $memberStamp->jumlah_stamp >= $target;
            $kuotaHabis = $program->maksimal_redeem_tercapai;
            $bisaTukar = $penuh && ! $kuotaHabis;
        @endphp

        {{-- Punch-card progress --}}
        <div class="mt-5 overflow-hidden rounded-3xl bg-gradient-to-br from-[#5a3c27] to-[#8b5e3c] p-5 text-white shadow-sm {{ $penuh ? 'stamp-card-penuh' : '' }}"
             id="stamp-program-data"
             data-nama-program="{{ $program->nama_program }}"
             data-current="{{ $current }}"
             data-target="{{ $target }}"
             data-penuh="{{ $penuh ? '1' : '0' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-white/60">Program Stamp</p>
                    <p class="mt-0.5 truncate text-base font-extrabold">{{ $program->nama_program }}</p>
                    @if ($program->deskripsi)
                        <p class="mt-1 text-xs leading-relaxed text-white/75">{{ $program->deskripsi }}</p>
                    @endif
                </div>
                <span class="stamp-badge-icon flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/15 p-2">
                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                </span>
            </div>

            <p class="mt-4 text-[11px] text-white/70">
                Setiap belanja min. Rp{{ number_format($program->minimal_pembelian, 0, ',', '.') }} = 1 stamp
            </p>

            {{-- Stamp dots pop in one by one (staggered) as they load, so a filled
                 punch-card feels alive instead of just appearing all at once. --}}
            <div class="mt-4 grid grid-cols-5 md:grid-cols-10 gap-2.5">
                @for ($i = 1; $i <= $target; $i++)
                    @php
                        $terisi = $i <= $current;
                    @endphp
                    <span
                        class="flex aspect-square items-center justify-center overflow-hidden rounded-full {{ $terisi ? 'stamp-dot-fill bg-white text-brand-800 shadow-sm' : 'border-2 border-dashed border-white/35 text-white/35' }}"
                        @if ($terisi) style="animation-delay: {{ ($i - 1) * 0.09 }}s" @endif
                    >
                        @if ($terisi)
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain p-2">
                        @else
                            @include('mobile.components.icon-cake', ['class' => 'h-4 w-4'])
                        @endif
                    </span>
                @endfor
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-white/15 pt-3">
                <p class="text-xs font-bold text-white">{{ $current }}/{{ $target }} stamp</p>
                @if ($penuh)
                    <p class="text-xs font-bold text-white">Stamp penuh, yuk tukar! 🎉</p>
                @else
                    <p class="text-xs text-white/75">{{ $target - $current }} stamp lagi untuk tukar hadiah</p>
                @endif
            </div>
        </div>

        @if ($kuotaHabis)
            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-700">
                Kuota penukaran program stamp ini sudah habis untuk saat ini.
            </div>
        @endif

        {{-- Section header --}}
        <div class="mt-6 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900">Tukar Hadiah</h2>
            <span class="text-xs text-gray-400">{{ $program->hadiah->count() }} pilihan</span>
        </div>

        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 space-y-0">
            @forelse ($program->hadiah as $hadiah)
                @php
                    $produk = $hadiah->produk;
                    $isCroissant = str_contains(strtolower($produk?->nama_produk ?? ''), 'croissant');
                @endphp
                <div class="flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-3 shadow-sm {{ ! $bisaTukar ? 'opacity-60' : '' }}">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-50">
                        @if ($produk?->foto)
                            <img src="{{ asset('storage/'.$produk->foto) }}" alt="{{ $produk->nama_produk }}" class="h-full w-full object-cover">
                        @elseif ($isCroissant)
                            <span class="text-brand-800">@include('mobile.components.icon-cake', ['class' => 'h-6 w-6'])</span>
                        @else
                            <span class="text-brand-800">@include('mobile.components.icon-cake', ['class' => 'h-6 w-6'])</span>
                        @endif
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-gray-900">{{ $hadiah->jumlah }}x {{ $produk?->nama_produk ?? 'Produk tidak tersedia' }}</p>
                        <p class="text-xs text-gray-400">Gratis dengan stamp penuh</p>
                    </div>

                    <button type="button"
                        @if ($bisaTukar && $produk) onclick="bukaKonfirmasiTukarStamp({{ $produk->id }}, {{ $hadiah->jumlah }}, {{ \Illuminate\Support\Js::from($produk->nama_produk) }})" @else disabled @endif
                        class="shrink-0 rounded-full px-4 py-2 text-xs font-bold transition {{ $bisaTukar && $produk ? 'bg-[#5a3c27] text-white active:bg-[#4a3020]' : 'bg-gray-100 text-gray-400' }}">
                        Tukar
                    </button>
                </div>
            @empty
                <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center">
                    <p class="text-sm font-semibold text-gray-500">Belum ada hadiah untuk program ini.</p>
                </div>
            @endforelse
        </div>

        {{-- Shared confirmation dialog, filled in dynamically by bukaKonfirmasiTukarStamp() --}}
        <dialog id="konfirmasi-tukar-stamp" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto rounded-t-3xl border-0 p-0">
            <div class="flex shrink-0 justify-center pb-1 pt-3">
                <span class="h-1 w-10 rounded-full bg-gray-200"></span>
            </div>
            <div class="p-5 pt-2 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                    @include('mobile.components.icon-gift', ['class' => 'h-6 w-6'])
                </span>
                <p class="mt-3 text-sm font-semibold text-gray-800">Tukar <span id="konfirmasi-tukar-stamp-nama"></span>?</p>
                <p class="mt-1 text-xs text-gray-500">Stamp kamu akan dipotong {{ $target }} dan tidak bisa dibatalkan.</p>

                <form method="POST" action="{{ route('member.stamps.redeem') }}" class="mt-5 space-y-2 pb-2">
                    @csrf
                    <input type="hidden" name="produk_id" id="konfirmasi-tukar-stamp-produk-id">
                    <button type="submit" class="w-full rounded-xl bg-[#5a3c27] py-3 text-sm font-semibold text-white">Ya, Tukar Sekarang</button>
                    <button type="button" onclick="closeSheet('konfirmasi-tukar-stamp')" class="w-full rounded-xl py-3 text-sm font-medium text-gray-500">Batal</button>
                </form>
            </div>
        </dialog>

    @endif

    </div>

    {{-- Kept outside #stamp-tab-content: when this page is opened directly this still
         runs normally, and when the Vouchers page fetches this page for its Stamp tab,
         it defines the same function itself (see rewards/index.blade.php) since injected
         <script> tags don't execute. --}}
    <script>
        function bukaKonfirmasiTukarStamp(produkId, jumlah, namaProduk) {
            document.getElementById('konfirmasi-tukar-stamp-produk-id').value = produkId;
            document.getElementById('konfirmasi-tukar-stamp-nama').textContent = jumlah + 'x ' + namaProduk;
            openSheet('konfirmasi-tukar-stamp');
        }

        // Only runs when this page is opened directly (not when the Vouchers
        // page injects this markup via innerHTML — injected <script> tags don't
        // execute there, and that page triggers the animation itself, at the
        // moment the Stamp tab actually becomes visible).
        document.querySelectorAll('#stamp-tab-content .stamp-dot-fill').forEach((dot) => {
            dot.classList.add('stamp-pop');
        });
    </script>

@endsection