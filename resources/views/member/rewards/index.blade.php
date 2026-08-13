@extends('member.layout', ['title' => 'Vouchers'])

@section('content')

    <div class="page-sticky-header -mx-5 -mt-6 px-5 pb-3 pt-6 sm:-mx-8 sm:px-8 md:-mx-10 md:px-10">
        <h1 class="text-center text-lg font-extrabold text-gray-900">Vouchers</h1>
    </div>

    <div class="mt-3 flex items-center justify-center gap-1.5 rounded-2xl border border-gray-100 bg-brand-25 py-2.5 text-xs text-gray-500">
        Poin kamu saat ini:
        <span class="font-bold text-brand-800">{{ number_format($member->poin, 0, ',', '.') }}</span>
    </div>

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

    <div id="voucher-tab-viewport" class="relative flex flex-1 flex-col overflow-hidden">
        <div id="voucher-tab-track" class="flex flex-1 transition-transform duration-300 ease-out">

            {{-- Pane: Semua --}}
            <div class="voucher-tab-pane flex shrink-0 flex-col bg-white pt-5" data-pane-index="0">

                <button type="button" id="stamp-teaser-card" onclick="switchVoucherTab('stamp')"
                        class="mb-4 flex w-full items-center gap-3 overflow-hidden rounded-2xl bg-gradient-to-br from-[#5a3c27] to-[#8b5e3c] p-4 text-left text-white shadow-sm transition active:scale-[0.99]">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/15">
                        @include('member.components.icon-cake', ['class' => 'h-5 w-5'])
                    </span>
                    <div class="min-w-0 flex-1">
                        <p id="stamp-teaser-title" class="truncate text-sm font-extrabold">Program Stamp</p>
                        <p id="stamp-teaser-subtitle" class="mt-0.5 text-[11px] text-white/75">Kumpulkan stamp, tukar hadiah gratis!</p>
                    </div>
                    <span class="shrink-0 text-white/60">
                        @include('member.components.icon-chevron-right', ['class' => 'h-4 w-4'])
                    </span>
                </button>

                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">Katalog Reward</h2>
                    <span class="text-xs text-gray-400">{{ $rewards->count() }} reward</span>
                </div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 space-y-0">
                    @forelse ($rewards as $reward)
                        @include('member.components.reward-card', ['reward' => $reward, 'member' => $member])
                    @empty
                        @include('member.components.empty-state', [
                            'title' => 'Belum Ada Reward',
                            'description' => 'Nanti kalau ada reward baru, langsung nongol di sini.',
                            'icon' => 'icon-gift',
                            'iconBg' => 'bg-brand-50',
                            'iconColor' => 'text-brand-800',
                        ])
                    @endforelse
                </div>
            </div>

            {{-- Pane: Voucher (same $rewards collection, filtered to jenis=voucher) --}}
            @php $rewardsVoucher = $rewards->where('jenis', 'voucher'); @endphp
            <div class="voucher-tab-pane flex shrink-0 flex-col bg-white pt-5" data-pane-index="1">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900">Katalog Voucher</h2>
                    <span class="text-xs text-gray-400">{{ $rewardsVoucher->count() }} voucher</span>
                </div>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 space-y-0">
                    @forelse ($rewardsVoucher as $reward)
                        @include('member.components.reward-card', ['reward' => $reward, 'member' => $member])
                    @empty
                        @include('member.components.empty-state', [
                            'title' => 'Belum Ada Voucher',
                            'description' => 'Diskon-diskon menarik bakal muncul di tab ini duluan.',
                            'icon' => 'icon-ticket',
                            'iconBg' => 'bg-brand-50',
                            'iconColor' => 'text-brand-800',
                        ])
                    @endforelse
                </div>
            </div>

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

    <script>
        function bukaKonfirmasiTukarStamp(produkId, jumlah, namaProduk) {
            document.getElementById('konfirmasi-tukar-stamp-produk-id').value = produkId;
            document.getElementById('konfirmasi-tukar-stamp-nama').textContent = jumlah + 'x ' + namaProduk;
            openSheet('konfirmasi-tukar-stamp');
        }

        (function () {
            const tabs = ['semua', 'voucher', 'stamp'];
            const requested = new URLSearchParams(window.location.search).get('tab');
            let current = Math.max(0, tabs.indexOf(requested));
            let stampLoaded = false;

            const track = document.getElementById('voucher-tab-track');
            const indicator = document.getElementById('voucher-tab-indicator');
            const buttons = document.querySelectorAll('.voucher-tab');
            const panes = document.querySelectorAll('.voucher-tab-pane');
            const viewport = document.getElementById('voucher-tab-viewport');

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
                const current = data.dataset.current;
                const target = data.dataset.target;
                const penuh = data.dataset.penuh === '1';
                document.getElementById('stamp-teaser-title').textContent = nama;
                document.getElementById('stamp-teaser-subtitle').textContent = penuh
                    ? 'Stamp penuh, yuk tukar! 🎉'
                    : `${current}/${target} stamp terkumpul`;
            }

            let startX = 0;
            let dragging = false;

            viewport.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                dragging = true;
            }, { passive: true });

            viewport.addEventListener('touchend', (e) => {
                if (!dragging) return;
                dragging = false;
                const diffX = startX - e.changedTouches[0].clientX;
                if (diffX > 40 && current < tabs.length - 1) {
                    current += 1;
                    paint();
                } else if (diffX < -40 && current > 0) {
                    current -= 1;
                    paint();
                }
            }, { passive: true });

            paint();
            requestAnimationFrame(() => requestAnimationFrame(() => {
                track.style.transition = '';
            }));
            if (!stampLoaded) loadStampPane(false);
        })();
    </script>

@endsection