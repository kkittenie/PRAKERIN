{{-- Modal crop gambar slide welcome, dipakai bareng di create.blade.php & edit.blade.php --}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<x-ui.modal x-data="{ open: false }" @open-crop-welcome-slide-modal.window="open = true" :isOpen="false" class="max-w-[700px]">
    <div class="p-6">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h4 class="text-lg font-bold text-gray-800 dark:text-white">Crop Gambar Slide Welcome</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Atur area dan rasio gambar sebelum disimpan untuk tampilan terbaik di aplikasi.
                    Atur area gambar sebelum disimpan untuk tampilan terbaik di aplikasi.
                </p>
            </div>
        </div>

        {{-- Area Gambar Cropper --}}
        <div class="my-4 max-h-[440px] h-[360px] overflow-hidden rounded-xl bg-gray-900/5 dark:bg-gray-900 flex items-center justify-center border border-gray-200 dark:border-gray-700">
            <img id="crop-image-welcome-slide" src="" alt="Gambar untuk di-crop" class="block max-w-full max-h-full">
        </div>

        {{-- Toolbar: Rasio & Transformasi --}}
        {{-- Toolbar: Info Rasio Tetap & Transformasi --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700">
            {{-- Pilihan Rasio --}}
            <div class="flex items-center gap-1.5 text-xs font-semibold">
                <span class="text-gray-500 dark:text-gray-400 mr-1">Rasio:</span>
                <button type="button" onclick="gantiRasioWelcomeSlide(3/4, this)"
                    class="btn-rasio-welcome px-2.5 py-1.5 rounded-lg border border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-500/20 dark:text-brand-300 font-bold transition">
                    3:4 (Portrait)
                </button>
                <button type="button" onclick="gantiRasioWelcomeSlide(1, this)"
                    class="btn-rasio-welcome px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:border-brand-500 transition">
                    1:1 (Persegi)
                </button>
                <button type="button" onclick="gantiRasioWelcomeSlide(16/9, this)"
                    class="btn-rasio-welcome px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:border-brand-500 transition">
                    16:9
                </button>
                <button type="button" onclick="gantiRasioWelcomeSlide(NaN, this)"
                    class="btn-rasio-welcome px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:border-brand-500 transition">
                    Bebas
                </button>
            {{-- Info Rasio Tetap Sesuai Tampilan --}}
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <rect x="4" y="2" width="16" height="20" rx="2" stroke-width="2"/>
                    </svg>
                    <span>Rasio: 4:5 (Portrait Slide Welcome)</span>
                </span>
            </div>

            {{-- Tombol Aksi: Rotasi & Reset --}}
            <div class="flex items-center gap-1">
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="rotasiWelcomeSlide(-90)" title="Putar Kiri 90°"
                    class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-3-3m3 3l3-3M3 10l3-3m-3 3l3 3" />
                    </svg>
                    <span>Putar Kiri</span>
                </button>
                <button type="button" onclick="rotasiWelcomeSlide(90)" title="Putar Kanan 90°"
                    class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m0 0l3-3m-3 3l-3-3m17-7l-3-3m3 3l-3 3" />
                    </svg>
                    <span>Putar Kanan</span>
                </button>
                <button type="button" onclick="resetCropperWelcomeSlide()" title="Reset Potongan"
                    class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Reset
                </button>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
            <button type="button" @click="open = false" onclick="batalCropWelcomeSlide()"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                Batal
            </button>
            <button type="button" @click="open = false" onclick="terapkanCropWelcomeSlide()"
                class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-600 shadow-sm transition">
                Terapkan Crop
            </button>
        </div>
    </div>
</x-ui.modal>

<script>
    let cropperWelcomeSlide = null;
    let cropInputElWelcomeSlide = null;
    let cropOriginalFileNameWelcomeSlide = '';
    let cropOriginalFileTypeWelcomeSlide = 'image/jpeg';
    let currentRasioWelcomeSlide = 3 / 4;
    const RASIO_WELCOME_SLIDE = 4 / 5; // 4:5 Portrait sesuai tampilan layar penuh & mobile

    function bukaCropWelcomeSlide(input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        cropInputElWelcomeSlide = input;
        cropOriginalFileNameWelcomeSlide = file.name;
        cropOriginalFileTypeWelcomeSlide = file.type || 'image/jpeg';

        const reader = new FileReader();
        reader.onload = function(e) {
            mulaiCropperWelcomeSlide(e.target.result);
        };
        reader.readAsDataURL(file);
    }

    function bukaCropDariPreviewWelcomeSlide() {
        const previewImg = document.getElementById('preview-gambar-slide');
        if (!previewImg || !previewImg.src) return;

        cropInputElWelcomeSlide = document.getElementById('input-gambar-slide');
        if (cropInputElWelcomeSlide && cropInputElWelcomeSlide.files && cropInputElWelcomeSlide.files[0]) {
            cropOriginalFileNameWelcomeSlide = cropInputElWelcomeSlide.files[0].name;
            cropOriginalFileTypeWelcomeSlide = cropInputElWelcomeSlide.files[0].type || 'image/jpeg';
        } else {
            const isPng = previewImg.src.toLowerCase().includes('.png');
            cropOriginalFileTypeWelcomeSlide = isPng ? 'image/png' : 'image/jpeg';
            cropOriginalFileNameWelcomeSlide = 'welcome-slide.' + (isPng ? 'png' : 'jpg');
        }

        mulaiCropperWelcomeSlide(previewImg.src);
    }

    function mulaiCropperWelcomeSlide(sumberGambar) {
        const img = document.getElementById('crop-image-welcome-slide');
        img.src = sumberGambar;

        window.dispatchEvent(new CustomEvent('open-crop-welcome-slide-modal'));

        setTimeout(() => {
            if (cropperWelcomeSlide) {
                cropperWelcomeSlide.destroy();
            }
            cropperWelcomeSlide = new Cropper(img, {
                aspectRatio: currentRasioWelcomeSlide,
                aspectRatio: RASIO_WELCOME_SLIDE,
                viewMode: 1,
                autoCropArea: 0.95,
                responsive: true,
                background: false,
            });
        }, 120);
    }

    function gantiRasioWelcomeSlide(rasio, btn) {
        currentRasioWelcomeSlide = rasio;
        if (cropperWelcomeSlide) {
            cropperWelcomeSlide.setAspectRatio(rasio);
        }
        document.querySelectorAll('.btn-rasio-welcome').forEach(b => {
            b.classList.remove('border-brand-500', 'bg-brand-50', 'text-brand-600', 'dark:bg-brand-500/20', 'dark:text-brand-300', 'font-bold');
            b.classList.add('border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-600', 'dark:text-gray-300');
        });
        if (btn) {
            btn.classList.add('border-brand-500', 'bg-brand-50', 'text-brand-600', 'dark:bg-brand-500/20', 'dark:text-brand-300', 'font-bold');
            btn.classList.remove('border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-600', 'dark:text-gray-300');
        }
    }

    function rotasiWelcomeSlide(derajat) {
        if (cropperWelcomeSlide) cropperWelcomeSlide.rotate(derajat);
    }

    function resetCropperWelcomeSlide() {
        if (cropperWelcomeSlide) cropperWelcomeSlide.reset();
    }

    function terapkanCropWelcomeSlide() {
        if (!cropperWelcomeSlide || !cropInputElWelcomeSlide) return;

        cropperWelcomeSlide.getCroppedCanvas({ maxWidth: 2048, maxHeight: 2048 }).toBlob((blob) => {
            if (!blob) return;

            const ekstensi = cropOriginalFileTypeWelcomeSlide.includes('png') ? 'png' : 'jpg';
            const namaFileHasil = (cropOriginalFileNameWelcomeSlide || 'slide').replace(/\.[^./\\]+$/, '') + '-cropped.' + ekstensi;
            const fileHasil = new File([blob], namaFileHasil, { type: cropOriginalFileTypeWelcomeSlide });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(fileHasil);
            cropInputElWelcomeSlide.files = dataTransfer.files;

            const urlHasil = URL.createObjectURL(blob);
            window.dispatchEvent(new CustomEvent('welcome-slide-cropped', {
                detail: { file: fileHasil, url: urlHasil, nama: namaFileHasil }
            }));

            cropperWelcomeSlide.destroy();
            cropperWelcomeSlide = null;
        }, cropOriginalFileTypeWelcomeSlide, 0.92);
    }

    function batalCropWelcomeSlide() {
        if (cropperWelcomeSlide) {
            cropperWelcomeSlide.destroy();
            cropperWelcomeSlide = null;
        }
    }
</script>
