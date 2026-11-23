{{-- Modal crop gambar banner, dipakai bareng di create.blade.php & edit.blade.php --}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<x-ui.modal x-data="{ open: false }" @open-crop-banner-modal.window="open = true" :isOpen="false" class="max-w-[640px]">
    <div class="p-6">
        <h4 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white">Crop Gambar Banner</h4>
        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
            Atur area gambar yang ingin ditampilkan sebelum disimpan.
        </p>

        <div class="mb-4 max-h-[420px] overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-900">
            <img id="crop-image-banner" src="" alt="Gambar untuk di-crop" class="block max-w-full">
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" @click="open = false" onclick="batalCropBanner()"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                Batal
            </button>
            <button type="button" @click="open = false" onclick="terapkanCropBanner()"
                class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
                Terapkan Crop
            </button>
        </div>
    </div>
</x-ui.modal>

<script>
    let cropperBanner = null;
    let cropInputElBanner = null;
    let cropOriginalFileNameBanner = '';
    let cropOriginalFileTypeBanner = 'image/jpeg';

    const RASIO_BANNER_MOBILE = 390 / 320;

    function bukaCropBanner(input) {
        if (!input.files || !input.files[0]) {
            return;
        }

        const file = input.files[0];
        cropInputElBanner = input;
        cropOriginalFileNameBanner = file.name;
        cropOriginalFileTypeBanner = file.type === 'image/png' ? 'image/png' : 'image/jpeg';

        const reader = new FileReader();
        reader.onload = function (e) {
            mulaiCropperBanner(e.target.result);
        };
        reader.readAsDataURL(file);
    }

    function bukaCropDariPreview() {
        const preview = document.getElementById('preview-gambar');
        if (!preview || preview.classList.contains('hidden') || !preview.src) {
            return;
        }

        cropInputElBanner = document.getElementById('input-gambar');

        if (cropInputElBanner.files && cropInputElBanner.files[0]) {
            cropOriginalFileNameBanner = cropInputElBanner.files[0].name;
            cropOriginalFileTypeBanner = cropInputElBanner.files[0].type === 'image/png' ? 'image/png' : 'image/jpeg';
        } else {
            cropOriginalFileTypeBanner = preview.src.toLowerCase().includes('.png') ? 'image/png' : 'image/jpeg';
            cropOriginalFileNameBanner = 'banner.' + (cropOriginalFileTypeBanner === 'image/png' ? 'png' : 'jpg');
        }

        mulaiCropperBanner(preview.src);
    }

    function mulaiCropperBanner(sumberGambar) {
        const img = document.getElementById('crop-image-banner');
        img.src = sumberGambar;

        window.dispatchEvent(new CustomEvent('open-crop-banner-modal'));

        setTimeout(() => {
            if (cropperBanner) {
                cropperBanner.destroy();
            }
            cropperBanner = new Cropper(img, {
                aspectRatio: RASIO_BANNER_MOBILE,
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                background: false,
            });
        }, 100);
    }

    function terapkanCropBanner() {
        if (!cropperBanner || !cropInputElBanner) {
            return;
        }

        cropperBanner.getCroppedCanvas({ maxWidth: 1920, maxHeight: 1920 }).toBlob((blob) => {
            if (!blob) {
                return;
            }

            const ekstensi = cropOriginalFileTypeBanner === 'image/png' ? 'png' : 'jpg';
            const namaFileHasil = cropOriginalFileNameBanner.replace(/\.[^./\\]+$/, '') + '-cropped.' + ekstensi;
            const fileHasil = new File([blob], namaFileHasil, { type: cropOriginalFileTypeBanner });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(fileHasil);
            cropInputElBanner.files = dataTransfer.files;

            const preview = document.getElementById('preview-gambar');
            const icon = document.getElementById('icon-gambar-kosong');
            const overlay = document.getElementById('overlay-crop-preview');
            const namaFileLabel = document.getElementById('nama-file-gambar');

            if (preview) {
                preview.src = URL.createObjectURL(blob);
                preview.classList.remove('hidden');
            }
            if (icon) {
                icon.classList.add('hidden');
            }
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }
            if (namaFileLabel) {
                namaFileLabel.textContent = namaFileHasil;
            }

            cropperBanner.destroy();
            cropperBanner = null;
        }, cropOriginalFileTypeBanner, 0.92);
    }

    function batalCropBanner() {
        if (cropperBanner) {
            cropperBanner.destroy();
            cropperBanner = null;
        }
        if (cropInputElBanner) {
            cropInputElBanner.value = '';
        }
    }
</script>