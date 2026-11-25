{{-- Form QRIS, dipakai bareng create & edit. Variabel: $qris (null saat tambah) --}}
@php $qris = $qris ?? null; @endphp

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
        <p class="font-bold">Mohon lengkapi atau perbaiki data berikut:</p>
        <ul class="mt-1 list-disc space-y-0.5 pl-5 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6" data-qris-form data-wajib-gambar="{{ $qris ? '0' : '1' }}">

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama <span class="text-red-500">*</span></label>
        <input type="text" name="nama" value="{{ old('nama', $qris?->nama) }}" maxlength="100" required
            placeholder="Contoh: QRIS BCA"
            class="h-12 w-full rounded-lg border {{ $errors->has('nama') ? 'border-red-500' : 'border-gray-300 dark:border-gray-700' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        @error('nama') <small class="mt-1 block text-red-500">{{ $message }}</small> @enderror
        <small class="mt-1 hidden text-red-500" data-qris-error="nama"></small>
    </div>

    <div class="md:col-span-2" x-data="{ preview: @js($qris?->gambar ? asset('storage/'.$qris->gambar) : null), nama: '' }">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Gambar QRIS @if (! $qris) <span class="text-red-500">*</span> @endif
        </label>

        <div class="flex items-start gap-4">
            <div class="flex h-40 w-40 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                <img x-show="preview" x-cloak :src="preview" alt="Preview QRIS" class="h-full w-full object-contain">
                <span x-show="! preview" class="px-2 text-center text-xs text-gray-400">Belum ada gambar</span>
            </div>

            <div class="min-w-0 flex-1">
                <label for="input-gambar-qris"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                    {{ $qris ? 'Ganti Gambar' : 'Pilih Gambar' }}
                </label>
                <input id="input-gambar-qris" type="file" name="gambar" accept="image/png,image/jpeg,image/webp" class="hidden"
                    @change="const f = $event.target.files[0]; if (f) { nama = f.name; preview = URL.createObjectURL(f); }">
                <p class="mt-2 truncate text-xs text-gray-500 dark:text-gray-400" x-text="nama || '{{ $qris ? 'Kosongkan jika tidak ingin mengganti gambar.' : 'Belum ada gambar dipilih.' }}'"></p>
                <span class="mt-2 block text-xs text-gray-400 dark:text-gray-500">
                    Tampil di halaman "Cara Pembayaran" pesanan online member. Format jpg/png/webp, maks 2MB.
                </span>
                @error('gambar') <small class="mt-1 block text-red-500">{{ $message }}</small> @enderror
                <small class="mt-1 hidden text-red-500" data-qris-error="gambar"></small>
            </div>
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <div class="flex items-center gap-3 h-10">
            <input type="hidden" name="status_aktif" value="0">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="status_aktif" value="1" class="sr-only peer"
                    {{ (string) old('status_aktif', $qris ? ($qris->status_aktif ? '1' : '0') : '1') === '1' ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>
                <span class="ml-3 text-sm font-semibold text-gray-800 dark:text-gray-200">Status aktif</span>
            </label>
        </div>
        <small class="text-gray-400">Hanya satu QRIS yang bisa aktif, mengaktifkan ini otomatis menonaktifkan QRIS lainnya.</small>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const container = document.querySelector('[data-qris-form]');
        const form = container ? container.closest('form') : null;
        if (!form) return;

        form.setAttribute('novalidate', 'novalidate');
        const wajibGambar = container.dataset.wajibGambar === '1';
        const inputNama = form.querySelector('input[name="nama"]');
        const inputGambar = form.querySelector('#input-gambar-qris');

        function setError(key, msg) {
            const errEl = form.querySelector(`[data-qris-error="${key}"]`);
            if (errEl) {
                errEl.textContent = msg || '';
                errEl.classList.toggle('hidden', !msg);
            }
            if (key === 'nama' && inputNama) {
                inputNama.classList.toggle('border-red-500', !!msg);
            }
        }

        if (inputNama) {
            inputNama.addEventListener('input', () => setError('nama', ''));
        }
        if (inputGambar) {
            inputGambar.addEventListener('change', () => setError('gambar', ''));
        }

        form.addEventListener('submit', function (e) {
            let pertamaGagal = null;

            const namaVal = inputNama ? inputNama.value.trim() : '';
            if (!namaVal) {
                setError('nama', 'Nama QRIS wajib diisi.');
                pertamaGagal = pertamaGagal || inputNama;
            } else {
                setError('nama', '');
            }

            const file = inputGambar && inputGambar.files ? inputGambar.files[0] : null;
            if (wajibGambar && !file) {
                setError('gambar', 'Gambar QRIS wajib dipilih.');
                pertamaGagal = pertamaGagal || form.querySelector('label[for="input-gambar-qris"]');
            } else if (file && file.size > 2 * 1024 * 1024) {
                setError('gambar', 'Ukuran gambar QRIS maksimal 2MB.');
                pertamaGagal = pertamaGagal || form.querySelector('label[for="input-gambar-qris"]');
            } else {
                setError('gambar', '');
            }

            if (pertamaGagal) {
                e.preventDefault();
                if (typeof pertamaGagal.focus === 'function') pertamaGagal.focus();
                pertamaGagal.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    })();
</script>
@endpush
