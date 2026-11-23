{{-- Modal crop foto produk, dipakai bareng di create.blade.php & edit.blade.php --}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<x-ui.modal x-data="{ open: false }" @open-crop-produk-modal.window="open = true" :isOpen="false" class="max-w-[680px]">
    <div class="p-6">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h4 class="text-lg font-bold text-gray-800 dark:text-white">Crop Foto Produk</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Atur potongan foto produk agar tampil presisi dan rapi di katalog & POS.
                </p>
            </div>
        </div>

        {{-- Area Gambar Cropper --}}
        <div class="my-4 max-h-[440px] h-[360px] overflow-hidden rounded-xl bg-gray-900/5 dark:bg-gray-900 flex items-center justify-center border border-gray-200 dark:border-gray-700">
            <img id="crop-image-produk" src="" alt="Foto produk untuk di-crop" class="block max-w-full max-h-full">
        </div>

        {{-- Toolbar: Rasio & Transformasi --}}
        {{-- Toolbar: Info Rasio Tetap & Transformasi --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700">
            {{-- Pilihan Rasio --}}
            <div class="flex items-center gap-1.5 text-xs font-semibold">
                <span class="text-gray-500 dark:text-gray-400 mr-1">Rasio:</span>
                <button type="button" onclick="gantiRasioProduk(1, this)"
                    class="btn-rasio-produk px-2.5 py-1.5 rounded-lg border border-brand-500 bg-brand-50 text-brand-600 dark:bg-brand-500/20 dark:text-brand-300 font-bold transition">
                    1:1 (Persegi)
                </button>
                <button type="button" onclick="gantiRasioProduk(4/3, this)"
                    class="btn-rasio-produk px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:border-brand-500 transition">
                    4:3
                </button>
                <button type="button" onclick="gantiRasioProduk(NaN, this)"
                    class="btn-rasio-produk px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:border-brand-500 transition">
                    Bebas
                </button>
            {{-- Info Rasio Tetap Sesuai Tampilan --}}
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/>
                    </svg>
                    <span>Rasio: 1:1 (Persegi Katalog & POS)</span>
                </span>
            </div>

            {{-- Tombol Aksi: Rotasi & Reset --}}
            <div class="flex items-center gap-1">
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="rotasiProduk(-90)" title="Putar Kiri 90°"
                    class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-3-3m3 3l3-3M3 10l3-3m-3 3l3 3" />
                    </svg>
                    <span>Putar Kiri</span>
                </button>
                <button type="button" onclick="rotasiProduk(90)" title="Putar Kanan 90°"
                    class="p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m0 0l3-3m-3 3l-3-3m17-7l-3-3m3 3l-3 3" />
                    </svg>
                    <span>Putar Kanan</span>
                </button>
                <button type="button" onclick="resetCropperProduk()" title="Reset Potongan"
                    class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Reset
                </button>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
            <button type="button" @click="open = false" onclick="batalCropProduk()"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                Batal
            </button>
            <button type="button" @click="open = false" onclick="terapkanCropProduk()"
                class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-600 shadow-sm transition">
                Terapkan Crop
            </button>
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
    let currentRasioProduk = 1; // Default 1:1 Persegi
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
        reader.onload = function(e) {
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
        img.src = sumberGambar;

        window.dispatchEvent(new CustomEvent('open-crop-produk-modal'));

        setTimeout(() => {
            if (cropperProduk) {
                cropperProduk.destroy();
            }
            cropperProduk = new Cropper(img, {
                aspectRatio: currentRasioProduk,
                aspectRatio: RASIO_FOTO_PRODUK,
                viewMode: 1,
                autoCropArea: 0.95,
                responsive: true,
                background: false,
            });
        }, 120);
    }

    function gantiRasioProduk(rasio, btn) {
        currentRasioProduk = rasio;
        if (cropperProduk) {
            cropperProduk.setAspectRatio(rasio);
        }
        document.querySelectorAll('.btn-rasio-produk').forEach(b => {
            b.classList.remove('border-brand-500', 'bg-brand-50', 'text-brand-600', 'dark:bg-brand-500/20', 'dark:text-brand-300', 'font-bold');
            b.classList.add('border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-600', 'dark:text-gray-300');
        });
        if (btn) {
            btn.classList.add('border-brand-500', 'bg-brand-50', 'text-brand-600', 'dark:bg-brand-500/20', 'dark:text-brand-300', 'font-bold');
            btn.classList.remove('border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-600', 'dark:text-gray-300');
        }
    }

    function rotasiProduk(derajat) {
        if (cropperProduk) cropperProduk.rotate(derajat);
    }

    function resetCropperProduk() {
        if (cropperProduk) cropperProduk.reset();
    }

    function terapkanCropProduk() {
        if (!cropperProduk || !cropInputElProduk) return;

        cropperProduk.getCroppedCanvas({ maxWidth: 2048, maxHeight: 2048 }).toBlob((blob) => {
            if (!blob) return;

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
        }, cropOriginalFileTypeProduk, 0.95);
    }

    function batalCropProduk() {
        if (cropperProduk) {
            cropperProduk.destroy();
            cropperProduk = null;
        }
    }
</script>
