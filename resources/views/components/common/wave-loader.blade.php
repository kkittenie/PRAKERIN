@props([
    'subtitle' => 'Freshly Baked Everyday',
])

{{-- Cek secara instan sebelum render DOM: lewati animasi jika hanya reload / aksi di halaman yang sama --}}
<script>
    (function () {
        try {
            var curr = window.location.pathname;
            var last = sessionStorage.getItem('daisy_nav_page');
            if (last && last === curr) {
                document.write('<style>#daisy-wave-loader{display:none!important;pointer-events:none!important;visibility:hidden!important;}</style>');
            }
        } catch (e) {}
    })();
</script>

<div id="daisy-wave-loader"
     class="daisy-wave-loader"
     aria-hidden="true"
     role="presentation">
    <div class="daisy-wave-curtain">
        {{-- Animated triple-layer water wave crest at the top edge of the descending curtain --}}
        <div class="daisy-wave-crest">
            <svg class="daisy-wave-svg daisy-wave-svg--back" viewBox="0 0 1440 136" preserveAspectRatio="none">
                <path d="M0,58 C180,126 360,2 540,58 C720,124 900,4 1080,58 C1260,118 1350,18 1440,58 L1440,136 L0,136 Z" fill="#8E1E26" fill-opacity="0.78"/>
            </svg>
            <svg class="daisy-wave-svg daisy-wave-svg--mid" viewBox="0 0 1440 136" preserveAspectRatio="none">
                <path d="M0,74 C200,8 400,128 600,68 C800,6 1000,126 1200,68 C1320,34 1390,56 1440,74 L1440,136 L0,136 Z" fill="#F9D6E0" fill-opacity="0.42"/>
            </svg>
            <svg class="daisy-wave-svg daisy-wave-svg--front" viewBox="0 0 1440 136" preserveAspectRatio="none">
                <path d="M0,72 C160,12 320,124 480,72 C640,16 800,124 960,72 C1120,16 1280,124 1440,72 L1440,136 L0,136 Z" fill="#3A221B"/>
            </svg>
        </div>

        {{-- Solid descending liquid body covering the screen initially --}}
        <div class="daisy-wave-body">
            <div class="daisy-wave-brand">
                <span class="daisy-wave-medallion">
                    <img src="{{ asset('images/logo/logo-icon.svg') }}" alt="Daisy Bakery" class="w-11 h-11 object-contain">
                </span>
                <img src="{{ asset('images/logo/logo-wordmark-light.svg') }}" alt="Daisy Bakery" class="h-7 w-auto object-contain mt-3">
                <span class="mt-2 inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.22em] text-[#EACE80]/85">
                    <img src="{{ asset('images/decor/strawberry-1.svg') }}" alt="" class="w-3.5 h-3.5">
                    <span>{{ $subtitle }}</span>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const loader = document.getElementById('daisy-wave-loader');
        if (!loader || loader.dataset.bound === '1') return;
        loader.dataset.bound = '1';

        const curr = window.location.pathname;
        const last = sessionStorage.getItem('daisy_nav_page');
        sessionStorage.setItem('daisy_nav_page', curr);

        // Hanya jalankan animasi wave loader jika PINDAH HALAMAN (pathname berbeda).
        // Jika tetap di halaman yang sama (upload bukti, konfirmasi, aksi form back, reload), langsung selesai tanpa animasi.
        if (last && last === curr) {
            loader.classList.add('is-done');
            loader.style.display = 'none';
            return;
        }

        const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let drained = false;

        function startDrain() {
            if (drained) return;
            drained = true;
            if (reduced) {
                loader.classList.add('is-done');
                return;
            }
            // Start lowering the wavy liquid curtain to progressively reveal the page from top to bottom
            requestAnimationFrame(() => {
                loader.classList.add('is-draining');
            });
            setTimeout(() => {
                loader.classList.add('is-done');
            }, 960);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => setTimeout(startDrain, 110), { once: true });
        } else {
            setTimeout(startDrain, 90);
        }

        // BFCache safety & hard fallback so loader can never get stuck
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) loader.classList.add('is-done');
        });
        setTimeout(startDrain, 1100);
    })();
</script>
