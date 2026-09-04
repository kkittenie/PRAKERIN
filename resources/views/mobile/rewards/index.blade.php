@extends('mobile.layout', ['title' => 'Vouchers'])

@section('content')

    {{-- ═══════════════════════════════════════════════════════════════════════
         MOBILE LAYOUT (unchanged, hidden on lg+)
         ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:hidden">
        <div class="page-sticky-header -mx-5 -mt-6 px-5 pb-3 pt-6 sm:-mx-8 sm:px-8 md:-mx-10 md:px-10">
            <h1 class="text-center text-lg font-extrabold text-gray-900">Vouchers</h1>
        </div>

        <div class="mt-3 flex items-center justify-center gap-1.5 rounded-2xl border border-gray-100 bg-brand-25 py-2.5 text-xs text-gray-500">
            Poin kamu saat ini:
            <span class="font-bold text-brand-800">{{ number_format($member->poin, 0, ',', '.') }}</span>
        </div>

        {{-- Fore-style underline tabs --}}
        <div class="relative mt-5 grid grid-cols-3 border-b border-gray-100">
            @foreach (['semua' => 'Semua', 'voucher' => 'Voucher', 'stamp' => 'Stamp'] as $key => $label)
                <button type="button" data-tab="{{ $key }}" onclick="switchVoucherTab('{{ $key }}')"
                        class="voucher-tab relative pb-3 text-center text-[13px] font-bold transition {{ $key === 'semua' ? 'text-brand-800' : 'text-gray-400 hover:text-gray-600' }}">
                    {{ $label }}
                </button>
            @endforeach
            <span id="voucher-tab-indicator"
                  class="pointer-events-none absolute -bottom-px left-0 h-[2.5px] w-1/3 rounded-full bg-brand-800 transition-transform duration-300 ease-out"
                  style="transform: translateX(0%);"></span>
        </div>

        {{-- Swipeable panes --}}
        <div id="voucher-tab-viewport" class="relative flex flex-1 flex-col overflow-hidden">
            <div id="voucher-tab-track" class="flex flex-1 transition-transform duration-300 ease-out">

                {{-- Pane: Semua --}}
                <div class="voucher-tab-pane flex shrink-0 flex-col bg-white pt-5" data-pane-index="0">
                    <button type="button" id="stamp-teaser-card" onclick="switchVoucherTab('stamp')"
                            class="mb-4 flex w-full items-center gap-3 overflow-hidden rounded-2xl bg-gradient-to-br from-[#5a3c27] to-[#8b5e3c] p-4 text-left text-white shadow-sm transition active:scale-[0.99]">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/15 p-2">
                            <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                        </span>
                        <div class="min-w-0 flex-1">
                            <p id="stamp-teaser-title" class="truncate text-sm font-extrabold">Program Stamp</p>
                            <p id="stamp-teaser-subtitle" class="mt-0.5 text-[11px] text-white/75">Kumpulin stamp, tukar hadiah gratis</p>
                        </div>
                        <span class="shrink-0 text-white/60">
                            @include('mobile.components.icon-chevron-right', ['class' => 'h-4 w-4'])
                        </span>
                    </button>

                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900">Katalog Reward</h2>
                        <span class="text-xs text-gray-400">{{ $rewards->count() }} reward</span>
                    </div>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 space-y-0">
                        @forelse ($rewards as $reward)
                            @include('mobile.components.reward-card', ['reward' => $reward, 'member' => $member])
                        @empty
                            @include('mobile.components.empty-state', [
                                'title' => 'Belum Ada Reward',
                                'description' => 'Nanti kalau ada reward baru, langsung nongol di sini.',
                                'icon' => 'icon-gift',
                                'iconBg' => 'bg-brand-50',
                                'iconColor' => 'text-brand-800',
                            ])
                        @endforelse
                    </div>
                </div>

                {{-- Pane: Voucher --}}
                @php $rewardsVoucher = $rewards->where('jenis', 'voucher'); @endphp
                <div class="voucher-tab-pane flex shrink-0 flex-col bg-white pt-5" data-pane-index="1">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-900">Katalog Voucher</h2>
                        <span class="text-xs text-gray-400">{{ $rewardsVoucher->count() }} voucher</span>
                    </div>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 space-y-0">
                        @forelse ($rewardsVoucher as $reward)
                            @include('mobile.components.reward-card', ['reward' => $reward, 'member' => $member])
                        @empty
                            @include('mobile.components.empty-state', [
                                'title' => 'Belum Ada Voucher',
                                'description' => 'Diskon-diskon menarik akan muncul di tab ini.',
                                'icon' => 'icon-ticket',
                                'iconBg' => 'bg-brand-50',
                                'iconColor' => 'text-brand-800',
                            ])
                        @endforelse
                    </div>
                </div>

                {{-- Pane: Stamp --}}
                <div class="voucher-tab-pane flex shrink-0 flex-col bg-white" data-pane-index="2">
                    <div id="stamp-pane-content" class="flex flex-1 flex-col">
                        <div class="flex flex-1 flex-col items-center justify-center py-10 text-center">
                            <svg class="h-6 w-6 animate-spin text-gray-300" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <p class="mt-3 text-xs text-gray-400">Memuat stamp...</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         DESKTOP LAYOUT (hidden below lg, completely different composition)
         Two-column grid: left sidebar (stamp + info), right main (catalog)
         ═══════════════════════════════════════════════════════════════════════ --}}
    @php $rewardsVoucherDesktop = $rewards->where('jenis', 'voucher'); @endphp
    <div class="hidden lg:block lg:max-w-6xl lg:mx-auto lg:my-6 lg:bg-white lg:rounded-3xl lg:border lg:border-gray-200/80 lg:shadow-xl lg:p-8 lg:space-y-8">
        {{-- Desktop Page Header --}}
        <div class="flex items-center justify-between border-b border-gray-100 pb-6">
            <div>
                <h1 class="text-2xl font-black text-brand-800 tracking-tight">Katalog Reward & Voucher</h1>
                <p class="text-xs text-gray-500 mt-1">Tukarkan poin dan stamp member Saisho Anda dengan voucher potongan & produk pilihan gratis</p>
            </div>

            {{-- Member Reward Summary Pill --}}
            <div class="flex items-center gap-3 rounded-2xl bg-brand-50 border border-brand-100 px-5 py-3 shadow-xs">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-800 text-white shadow-xs">
                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Saisho" class="h-6 w-6 object-contain">
                </span>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-brand-700">Saldo Poin Kamu</p>
                    <p class="text-lg font-black text-brand-900">{{ number_format($member->poin, 0, ',', '.') }} <span class="text-xs font-bold text-brand-700">Poin</span></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8 items-start">
            {{-- Left Sidebar (Stamp Program + Stats) --}}
            <div class="col-span-4 space-y-5 sticky top-24">
                {{-- Stamp Program Card (Desktop) --}}
                <div id="desktop-stamp-sidebar" class="space-y-4">
                    <div id="desktop-stamp-sidebar-content">
                        <button type="button" id="desktop-stamp-teaser-card" onclick="switchDesktopTab('stamp')"
                                class="flex w-full items-center gap-3.5 overflow-hidden rounded-2xl bg-gradient-to-br from-[#5a3c27] via-[#6f4b32] to-[#8b5e3c] p-5 text-left text-white shadow-md transition hover:shadow-lg active:scale-[0.99]">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20 p-2.5 backdrop-blur-sm border border-white/20">
                                <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="" class="h-full w-full object-contain">
                            </span>
                            <div class="min-w-0 flex-1">
                                <p id="desktop-stamp-teaser-title" class="truncate text-sm font-extrabold tracking-wide">Program Stamp</p>
                                <p id="desktop-stamp-teaser-subtitle" class="mt-0.5 text-[11px] text-white/80 font-medium">Kumpulin stamp, tukar hadiah gratis</p>
                            </div>
                            <span class="shrink-0 text-white/70">
                                @include('mobile.components.icon-chevron-right', ['class' => 'h-5 w-5'])
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Quick info Card --}}
                <div class="rounded-2xl border border-dashed border-brand-200/80 bg-brand-25/50 p-5 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-extrabold text-brand-800">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-brand-100 text-brand-800">💡</span>
                        <span>Cara Mendapatkan Poin</span>
                    </div>
                    <p class="text-xs leading-relaxed text-gray-600 font-medium">
                        Setiap transaksi pemesanan melalui aplikasi member Saisho akan otomatis memberikan poin reward. Kumpulkan poin dan tukarkan dengan berbagai penawaran menarik!
                    </p>
                </div>
            </div>

            {{-- Right Main: Reward Catalog --}}
            <div class="col-span-8 space-y-6">
                {{-- Desktop Tab filter pills --}}
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div class="inline-flex items-center gap-2 rounded-2xl bg-gray-100/80 p-1.5 border border-gray-200/60 shadow-inner">
                        <button type="button" data-desktop-tab="voucher" onclick="switchDesktopTab('voucher')"
                                class="desktop-filter-tab rounded-xl px-5 py-2.5 text-xs font-extrabold transition bg-brand-800 text-white shadow-sm outline-none focus:outline-none focus:ring-0 cursor-pointer">
                            Voucher Diskon
                        </button>
                        <button type="button" data-desktop-tab="stamp" onclick="switchDesktopTab('stamp')"
                                class="desktop-filter-tab rounded-xl px-5 py-2.5 text-xs font-bold transition bg-transparent text-gray-600 hover:text-gray-900 outline-none focus:outline-none focus:ring-0 cursor-pointer">
                            Stamp Reward
                        </button>
                    </div>
                    <span class="text-xs font-semibold text-gray-400">Pilih penawaran yang ingin ditukarkan</span>
                </div>

                {{-- Desktop: Voucher pane --}}
                <div id="desktop-pane-voucher" class="desktop-pane pt-2">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-extrabold uppercase tracking-wide text-gray-900">Katalog Voucher</h2>
                        <span class="text-xs text-gray-500 font-semibold">{{ $rewardsVoucherDesktop->count() }} voucher tersedia</span>
                    </div>
                    <div class="grid grid-cols-2 xl:grid-cols-3 gap-5">
                        @forelse ($rewardsVoucherDesktop as $reward)
                            @include('mobile.components.reward-card', ['reward' => $reward, 'member' => $member])
                        @empty
                            <div class="col-span-full">
                                @include('mobile.components.empty-state', [
                                    'title' => 'Belum Ada Voucher',
                                    'description' => 'Diskon-diskon menarik akan muncul di tab ini.',
                                    'icon' => 'icon-ticket',
                                    'iconBg' => 'bg-brand-50',
                                    'iconColor' => 'text-brand-800',
                                ])
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Desktop: Stamp pane --}}
                <div id="desktop-pane-stamp" class="desktop-pane pt-2 hidden">
                    <div id="desktop-stamp-pane-content" class="flex flex-1 flex-col">
                        <div class="flex flex-1 flex-col items-center justify-center py-12 text-center">
                            <svg class="h-8 w-8 animate-spin text-brand-800" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <p class="mt-3 text-xs font-bold text-gray-500">Memuat program stamp...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SHARED SCRIPTS (Mobile swipeable tabs + Desktop filter tabs)
         ═══════════════════════════════════════════════════════════════════════ --}}
    <script>
        window.bukaKonfirmasiTukarStamp = function (produkId, jumlah, namaProduk) {
            const hiddenInput = document.getElementById('konfirmasi-tukar-stamp-produk-id');
            const namaSpan = document.getElementById('konfirmasi-tukar-stamp-nama');
            if (hiddenInput) hiddenInput.value = produkId;
            if (namaSpan) namaSpan.textContent = jumlah + 'x ' + namaProduk;
            openSheet('konfirmasi-tukar-stamp');
        };

        window.bukaKonfirmasiTukarStampFromBtn = function (btn) {
            if (!btn) return;
            const produkId = btn.dataset.produkId;
            const jumlah = btn.dataset.jumlah;
            const namaProduk = btn.dataset.namaProduk;
            window.bukaKonfirmasiTukarStamp(produkId, jumlah, namaProduk);
        };

        (function () {
            // ─── Mobile swipeable tabs ───
            const tabs = ['semua', 'voucher', 'stamp'];
            const requested = new URLSearchParams(window.location.search).get('tab');
            let current = Math.max(0, tabs.indexOf(requested));
            let stampLoaded = false;

            const track = document.getElementById('voucher-tab-track');
            const indicator = document.getElementById('voucher-tab-indicator');
            const buttons = document.querySelectorAll('.voucher-tab');
            const panes = document.querySelectorAll('.voucher-tab-pane');
            const viewport = document.getElementById('voucher-tab-viewport');

            if (track && indicator && viewport) {
                const paneWidthPercent = 100 / tabs.length;
                track.style.width = (tabs.length * 100) + '%';
                panes.forEach((pane, i) => {
                    pane.style.width = paneWidthPercent + '%';
                    pane.style.pointerEvents = i === current ? 'auto' : 'none';
                });

                function paint() {
                    track.style.transform = `translateX(-${current * paneWidthPercent}%)`;
                    panes.forEach((pane, i) => {
                        pane.style.pointerEvents = i === current ? 'auto' : 'none';
                        pane.style.zIndex = i === current ? '1' : '0';
                    });
                    indicator.style.transform = `translateX(${current * 100}%)`;
                    buttons.forEach((btn, i) => {
                        btn.classList.toggle('text-brand-800', i === current);
                        btn.classList.toggle('text-gray-400', i !== current);
                    });
                    if (tabs[current] === 'stamp') {
                        if (!stampLoaded) {
                            loadStampPane(true);
                        } else {
                            animateStampDots();
                        }
                    }
                }

                window.switchVoucherTab = function (name) {
                    const idx = tabs.indexOf(name);
                    if (idx === -1 || idx === current) return;
                    current = idx;
                    paint();
                };

                track.style.transition = 'none';

                // Swipe/drag gesture
                let startX = 0;
                let dragging = false;
                viewport.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; dragging = true; }, { passive: true });
                viewport.addEventListener('touchend', (e) => {
                    if (!dragging) return;
                    dragging = false;
                    const diffX = startX - e.changedTouches[0].clientX;
                    if (diffX > 40 && current < tabs.length - 1) { current += 1; paint(); }
                    else if (diffX < -40 && current > 0) { current -= 1; paint(); }
                }, { passive: true });

                paint();
                requestAnimationFrame(() => requestAnimationFrame(() => { track.style.transition = ''; }));
                if (!stampLoaded) loadStampPane(false);
            }

            // ─── Desktop filter tabs ───
            let desktopCurrent = Math.max(0, tabs.indexOf(requested));
            let desktopStampLoaded = false;

            window.switchDesktopTab = function (name) {
                const idx = tabs.indexOf(name);
                if (idx === -1) return;
                desktopCurrent = idx;

                // Update pill buttons
                document.querySelectorAll('.desktop-filter-tab').forEach(btn => {
                    const isActive = btn.dataset.desktopTab === name;
                    btn.classList.toggle('bg-brand-800', isActive);
                    btn.classList.toggle('text-white', isActive);
                    btn.classList.toggle('shadow-sm', isActive);
                    btn.classList.toggle('font-extrabold', isActive);
                    btn.classList.toggle('font-bold', !isActive);
                    btn.classList.toggle('bg-transparent', !isActive);
                    btn.classList.toggle('text-gray-600', !isActive);
                    btn.classList.toggle('hover:text-gray-900', !isActive);
                });

                // Show/hide panes
                document.querySelectorAll('.desktop-pane').forEach(pane => pane.classList.add('hidden'));
                const target = document.getElementById('desktop-pane-' + name);
                if (target) target.classList.remove('hidden');

                // Load stamp if needed
                if (name === 'stamp' && !desktopStampLoaded) {
                    loadDesktopStampPane();
                } else if (name === 'stamp') {
                    animateDesktopStampDots();
                }
            };

            function loadDesktopStampPane() {
                desktopStampLoaded = true;
                fetch("{{ route('member.stamps.index') }}")
                    .then(res => res.text())
                    .then(html => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const fragment = doc.getElementById('stamp-tab-content');
                        const target = document.getElementById('desktop-stamp-pane-content');
                        if (fragment && target) {
                            target.innerHTML = fragment.innerHTML;
                            window.bindSheetDialogs(target);
                            updateDesktopStampTeaser(doc);
                            animateDesktopStampDots();
                        } else if (target) {
                            target.innerHTML = '<p class="flex-1 py-10 text-center text-xs text-gray-400">Gagal memuat stamp, coba lagi ya.</p>';
                        }
                    })
                    .catch(() => {
                        desktopStampLoaded = false;
                        const target = document.getElementById('desktop-stamp-pane-content');
                        if (target) target.innerHTML = '<p class="flex-1 py-10 text-center text-xs text-gray-400">Gagal memuat stamp, coba lagi ya.</p>';
                    });
            }

            function animateDesktopStampDots() {
                const container = document.getElementById('desktop-stamp-pane-content');
                if (!container) return;
                container.querySelectorAll('.stamp-dot-fill:not(.stamp-pop)').forEach(dot => dot.classList.add('stamp-pop'));
            }

            function updateDesktopStampTeaser(docOrEl) {
                const card = document.getElementById('desktop-stamp-teaser-card');
                if (!card) return;
                const data = docOrEl.querySelector('#stamp-program-data');
                if (!data) { card.closest('#desktop-stamp-sidebar-content').innerHTML = ''; return; }
                const nama = data.dataset.namaProgram;
                const cur = data.dataset.current;
                const tgt = data.dataset.target;
                const penuh = data.dataset.penuh === '1';
                document.getElementById('desktop-stamp-teaser-title').textContent = nama;
                document.getElementById('desktop-stamp-teaser-subtitle').textContent = penuh
                    ? 'Stamp penuh, yuk tukar! 🎉'
                    : `${cur}/${tgt} stamp terkumpul`;
            }

            // ─── Shared stamp helpers (mobile) ───
            function loadStampPane(shouldAnimate = false) {
                stampLoaded = true;
                fetch("{{ route('member.stamps.index') }}")
                    .then((res) => res.text())
                    .then((html) => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const fragment = doc.getElementById('stamp-tab-content');
                        const target = document.getElementById('stamp-pane-content');
                        if (fragment && target) {
                            target.innerHTML = fragment.innerHTML;
                            window.bindSheetDialogs(target);
                            updateStampTeaser(target);
                            if (shouldAnimate) animateStampDots();
                        } else if (target) {
                            target.innerHTML = '<p class="flex-1 py-10 text-center text-xs text-gray-400">Gagal memuat stamp, coba lagi ya.</p>';
                        }

                        // Also update desktop sidebar teaser if available
                        if (!desktopStampLoaded) {
                            desktopStampLoaded = true;
                            const desktopTarget = document.getElementById('desktop-stamp-pane-content');
                            if (fragment && desktopTarget) {
                                desktopTarget.innerHTML = fragment.innerHTML;
                            }
                            updateDesktopStampTeaser(doc);
                        }
                    })
                    .catch(() => {
                        stampLoaded = false;
                        const target = document.getElementById('stamp-pane-content');
                        if (target) target.innerHTML = '<p class="flex-1 py-10 text-center text-xs text-gray-400">Gagal memuat stamp, coba lagi ya.</p>';
                    });
            }

            function animateStampDots() {
                const container = document.getElementById('stamp-pane-content');
                if (!container) return;
                container.querySelectorAll('.stamp-dot-fill:not(.stamp-pop)').forEach((dot) => {
                    dot.classList.add('stamp-pop');
                });
            }

            function updateStampTeaser(paneEl) {
                const card = document.getElementById('stamp-teaser-card');
                if (!card) return;
                const data = paneEl.querySelector('#stamp-program-data');
                if (!data) {
                    card.remove();
                    return;
                }
                const nama = data.dataset.namaProgram;
                const cur = data.dataset.current;
                const tgt = data.dataset.target;
                const penuh = data.dataset.penuh === '1';
                document.getElementById('stamp-teaser-title').textContent = nama;
                document.getElementById('stamp-teaser-subtitle').textContent = penuh
                    ? 'Stamp penuh, yuk tukar! 🎉'
                    : `${cur}/${tgt} stamp terkumpul`;
            }

            // Desktop: prefetch stamp data on load (for sidebar teaser)
            if (!desktopStampLoaded && document.getElementById('desktop-stamp-sidebar')) {
                loadDesktopStampPane();
            }

            // Initial desktop tab if ?tab= is set
            if (requested && tabs.indexOf(requested) > 0) {
                switchDesktopTab(requested);
            }
        })();
    </script>

    {{-- Top-level single confirmation dialog for stamp redeem --}}
    <dialog id="konfirmasi-tukar-stamp" class="sheet fixed inset-x-0 bottom-0 top-auto m-0 w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto rounded-t-3xl border-0 p-0 z-50">
        <div class="flex shrink-0 justify-center pb-1 pt-3">
            <span class="h-1 w-10 rounded-full bg-gray-200"></span>
        </div>
        <div class="p-5 pt-2 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-800">
                @include('mobile.components.icon-gift', ['class' => 'h-6 w-6'])
            </span>
            <p class="mt-3 text-sm font-semibold text-gray-800">Tukar <span id="konfirmasi-tukar-stamp-nama"></span>?</p>
            <p class="mt-1 text-xs text-gray-500">Stamp kamu akan dipotong dan tidak bisa dibatalkan.</p>

            <form method="POST" action="{{ route('member.stamps.redeem') }}" class="mt-5 space-y-2 pb-2">
                @csrf
                <input type="hidden" name="produk_id" id="konfirmasi-tukar-stamp-produk-id">
                <button type="submit" class="w-full rounded-xl bg-[#5a3c27] py-3 text-sm font-semibold text-white hover:bg-[#4a3020]">Ya, Tukar Sekarang</button>
                <button type="button" onclick="closeSheet('konfirmasi-tukar-stamp')" class="w-full rounded-xl py-3 text-sm font-medium text-gray-500 hover:bg-gray-100">Batal</button>
            </form>
        </div>
    </dialog>

@endsection