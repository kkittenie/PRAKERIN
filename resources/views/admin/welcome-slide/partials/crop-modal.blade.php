{{-- Modal crop gambar slide welcome, dipakai bareng di create.blade.php & edit.blade.php --}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<x-ui.modal x-data="{ open: false }" @open-crop-welcome-slide-modal.window="open = true" @close-crop-welcome-slide-modal.window="open = false" :isOpen="false" class="max-w-[640px]">
    <div class="p-6">
        <div class="pb-3 pr-12 border-b border-gray-100 dark:border-gray-800">
            <h4 class="font-display text-lg font-bold text-gray-800 dark:text-white">Crop Gambar Slide Welcome</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Atur area gambar yang ingin ditampilkan sebelum disimpan (rasio tetap 4:5 sesuai tampilan slide welcome).
            </p>
        </div>

        {{-- Area Gambar Cropper --}}
        <div class="my-4 h-[340px] sm:h-[380px] max-h-[55vh] overflow-hidden rounded-xl bg-gray-900/10 dark:bg-gray-950 flex items-center justify-center border border-gray-200 dark:border-gray-700">
            <img id="crop-image-welcome-slide" src="" alt="Gambar untuk di-crop" class="block max-w-full max-h-full">
        </div>

        {{-- Footer Modal --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="rotasiWelcomeSlide(-90)" title="Putar Kiri 90°"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m0 0l-3-3m3 3l3-3M3 10l3-3m-3 3l3 3" />
                    </svg>
                    <span>Putar Kiri</span>
                </button>
                <button type="button" onclick="rotasiWelcomeSlide(90)" title="Putar Kanan 90°"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a5 5 0 00-5 5v2m0 0l3-3m-3 3l-3-3m17-7l-3-3m3 3l-3 3" />
                    </svg>
                    <span>Putar Kanan</span>
                </button>
                <button type="button" onclick="resetCropperWelcomeSlide()" title="Reset Potongan"
                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    Reset
                </button>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="open = false" onclick="batalCropWelcomeSlide()"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="button" onclick="terapkanCropWelcomeSlide()"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-600 shadow-sm transition cursor-pointer">
                    Terapkan Crop
                </button>
            </div>
        </div>
    </div>
</x-ui.modal>

<script>
    let cropperWelcomeSlide = null;
    let cropInputElWelcomeSlide = null;
    let cropOriginalFileNameWelcomeSlide = '';
    let cropOriginalFileTypeWelcomeSlide = 'image/jpeg';

    const RASIO_WELCOME_SLIDE = 4 / 5; // 4:5 Portrait sesuai tampilan slide welcome

    function bukaCropWelcomeSlide(input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        cropInputElWelcomeSlide = input;
        cropOriginalFileNameWelcomeSlide = file.name;
        cropOriginalFileTypeWelcomeSlide = file.type || 'image/jpeg';

        const reader = new FileReader();
        reader.onload = function (e) {
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
        if (!img) return;

        if (cropperWelcomeSlide) {
            cropperWelcomeSlide.destroy();
            cropperWelcomeSlide = null;
        }

        const initInstance = () => {
            setTimeout(() => {
                if (cropperWelcomeSlide) {
                    cropperWelcomeSlide.destroy();
                }
                cropperWelcomeSlide = new Cropper(img, {
                    aspectRatio: RASIO_WELCOME_SLIDE,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    background: false,
                });
            }, 150);
        };

        img.onload = initInstance;
        img.src = sumberGambar;

        window.dispatchEvent(new CustomEvent('open-crop-welcome-slide-modal'));

        if (img.complete && img.naturalWidth > 0) {
            img.onload = null;
            initInstance();
        }
    }

    function rotasiWelcomeSlide(derajat) {
        if (cropperWelcomeSlide) cropperWelcomeSlide.rotate(derajat);
    }

    function resetCropperWelcomeSlide() {
        if (cropperWelcomeSlide) cropperWelcomeSlide.reset();
    }

    function terapkanCropWelcomeSlide() {
        if (!cropperWelcomeSlide || !cropInputElWelcomeSlide) {
            window.dispatchEvent(new CustomEvent('close-crop-welcome-slide-modal'));
            return;
        }

        const canvas = cropperWelcomeSlide.getCroppedCanvas({ maxWidth: 2048, maxHeight: 2048 });
        if (!canvas) {
            window.dispatchEvent(new CustomEvent('close-crop-welcome-slide-modal'));
            return;
        }

        canvas.toBlob((blob) => {
            if (!blob) {
                window.dispatchEvent(new CustomEvent('close-crop-welcome-slide-modal'));
                return;
            }

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
            window.dispatchEvent(new CustomEvent('close-crop-welcome-slide-modal'));
        }, cropOriginalFileTypeWelcomeSlide, 0.92);
    }

    function batalCropWelcomeSlide() {
        if (cropperWelcomeSlide) {
            cropperWelcomeSlide.destroy();
            cropperWelcomeSlide = null;
        }
    }
</script>
