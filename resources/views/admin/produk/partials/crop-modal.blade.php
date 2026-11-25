{{-- Modal crop foto produk, dipakai bareng di create.blade.php & edit.blade.php --}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<x-ui.modal x-data="{ open: false }" @open-crop-produk-modal.window="open = true" @close-crop-produk-modal.window="open = false" :isOpen="false" class="max-w-[640px]">
    <div class="p-6">
        <div class="pb-3 pr-12 border-b border-gray-100 dark:border-gray-800">
            <h4 class="font-display text-lg font-bold text-gray-800 dark:text-white">Crop Foto Produk</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Atur area foto produk yang ingin ditampilkan sebelum disimpan (rasio tetap 1:1 sesuai katalog &amp; POS).
            </p>
        </div>

        {{-- Area Gambar Cropper --}}
        <div class="my-4 h-[340px] sm:h-[380px] max-h-[55vh] overflow-hidden rounded-xl bg-gray-900/10 dark:bg-gray-950 flex items-center justify-center border border-gray-200 dark:border-gray-700">
            <img id="crop-image-produk" src="" alt="Foto produk untuk di-crop" class="block max-w-full max-h-full">
        </div>

        {{-- Footer Modal --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="rotasiProduk(-90)" title="Putar Kiri 90°"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v2m0 0l-3-3m3 3l3-3M3 10l3-3m-3 3l3 3" />
                    </svg>
                    <span>Putar Kiri</span>
                </button>
                <button type="button" onclick="rotasiProduk(90)" title="Putar Kanan 90°"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a5 5 0 00-5 5v2m0 0l3-3m-3 3l-3-3m17-7l-3-3m3 3l-3 3" />
                    </svg>
                    <span>Putar Kanan</span>
                </button>
                <button type="button" onclick="resetCropperProduk()" title="Reset Potongan"
                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition cursor-pointer">
                    Reset
                </button>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="open = false" onclick="batalCropProduk()"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="button" onclick="terapkanCropProduk()"
                    class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-600 shadow-sm transition cursor-pointer">
                    Terapkan Crop
                </button>
            </div>
        </div>
    </div>
</x-ui.modal>

<script>
    let cropperProduk = null;
    let cropInputElProduk = null;
    let cropTargetTypeProduk = 'utama'; // 'utama', 'baris', atau 'varian'
    let cropTargetIndexProduk = null;
    let cropOriginalFileNameProduk = '';
    let cropOriginalFileTypeProduk = 'image/png';

    const RASIO_FOTO_PRODUK = 1; // 1:1 Persegi sesuai tampilan katalog & POS

    function bukaCropProduk(input, tipe = 'utama', index = null) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        cropInputElProduk = input;
        cropTargetTypeProduk = tipe;
        cropTargetIndexProduk = index;
        cropOriginalFileNameProduk = file.name;
        cropOriginalFileTypeProduk = file.type || 'image/png';

        const reader = new FileReader();
        reader.onload = function (e) {
            mulaiCropperProduk(e.target.result);
        };
        reader.readAsDataURL(file);
    }

    function bukaCropDariPreviewProduk(tipe = 'utama', index = null) {
        cropTargetTypeProduk = tipe;
        cropTargetIndexProduk = index;

        let previewSrc = null;
        if (tipe === 'utama') {
            cropInputElProduk = document.getElementById('input-foto-produk-utama');
            const previewImg = document.getElementById('preview-foto-produk-utama');
            if (previewImg) previewSrc = previewImg.src;
        }

        if (!previewSrc && cropInputElProduk && cropInputElProduk.files && cropInputElProduk.files[0]) {
            bukaCropProduk(cropInputElProduk, tipe, index);
            return;
        }

        if (previewSrc) {
            if (cropInputElProduk && cropInputElProduk.files && cropInputElProduk.files[0]) {
                cropOriginalFileNameProduk = cropInputElProduk.files[0].name;
                cropOriginalFileTypeProduk = cropInputElProduk.files[0].type || 'image/png';
            } else {
                const isJpg = previewSrc.toLowerCase().includes('.jpg') || previewSrc.toLowerCase().includes('.jpeg');
                cropOriginalFileTypeProduk = isJpg ? 'image/jpeg' : 'image/png';
                cropOriginalFileNameProduk = 'produk.' + (isJpg ? 'jpg' : 'png');
            }
            mulaiCropperProduk(previewSrc);
        }
    }

    function mulaiCropperProduk(sumberGambar) {
        const img = document.getElementById('crop-image-produk');
        if (!img) return;

        if (cropperProduk) {
            cropperProduk.destroy();
            cropperProduk = null;
        }

        const initInstance = () => {
            setTimeout(() => {
                if (cropperProduk) {
                    cropperProduk.destroy();
                }
                cropperProduk = new Cropper(img, {
                    aspectRatio: RASIO_FOTO_PRODUK,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    background: false,
                });
            }, 150);
        };

        img.onload = initInstance;
        img.src = sumberGambar;

        window.dispatchEvent(new CustomEvent('open-crop-produk-modal'));

        if (img.complete && img.naturalWidth > 0) {
            img.onload = null;
            initInstance();
        }
    }

    function rotasiProduk(derajat) {
        if (cropperProduk) cropperProduk.rotate(derajat);
    }

    function resetCropperProduk() {
        if (cropperProduk) cropperProduk.reset();
    }

    function terapkanCropProduk() {
        if (!cropperProduk || !cropInputElProduk) {
            window.dispatchEvent(new CustomEvent('close-crop-produk-modal'));
            return;
        }

        const canvas = cropperProduk.getCroppedCanvas({ maxWidth: 2048, maxHeight: 2048 });
        if (!canvas) {
            window.dispatchEvent(new CustomEvent('close-crop-produk-modal'));
            return;
        }

        canvas.toBlob((blob) => {
            if (!blob) {
                window.dispatchEvent(new CustomEvent('close-crop-produk-modal'));
                return;
            }

            const ekstensi = cropOriginalFileTypeProduk.includes('png') ? 'png' : (cropOriginalFileTypeProduk.includes('webp') ? 'webp' : 'jpg');
            const namaFileHasil = (cropOriginalFileNameProduk || 'produk').replace(/\.[^./\\]+$/, '') + '-cropped.' + ekstensi;
            const fileHasil = new File([blob], namaFileHasil, { type: cropOriginalFileTypeProduk });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(fileHasil);
            cropInputElProduk.files = dataTransfer.files;

            const urlHasil = URL.createObjectURL(blob);
            window.dispatchEvent(new CustomEvent('foto-produk-cropped', {
                detail: {
                    file: fileHasil,
                    url: urlHasil,
                    nama: namaFileHasil,
                    tipe: cropTargetTypeProduk,
                    index: cropTargetIndexProduk
                }
            }));

            cropperProduk.destroy();
            cropperProduk = null;
            window.dispatchEvent(new CustomEvent('close-crop-produk-modal'));
        }, cropOriginalFileTypeProduk, 0.95);
    }

    function batalCropProduk() {
        if (cropperProduk) {
            cropperProduk.destroy();
            cropperProduk = null;
        }
    }
</script>
