{{-- Form QRIS, dipakai bareng create & edit. Variabel: $qris (null saat tambah) --}}
@php $qris = $qris ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama <span class="text-red-500">*</span></label>
        <input type="text" name="nama" value="{{ old('nama', $qris?->nama) }}" maxlength="100"
            placeholder="Contoh: QRIS BCA"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        @error('nama') <small class="text-red-500">{{ $message }}</small> @enderror
    </div>

    <div class="md:col-span-2" x-data="{ preview: @js($qris?->gambar ? asset('storage/'.$qris->gambar) : null), nama: '' }">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Gambar QRIS @if (! $qris) <span class="text-red-500">*</span> @endif
        </label>

        <div class="flex items-start gap-4">
            <div class="flex h-40 w-40 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                <img x-show="preview" :src="preview" alt="Preview QRIS" class="h-full w-full object-contain">
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
                @error('gambar') <small class="text-red-500">{{ $message }}</small> @enderror
            </div>
        </div>
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <label class="flex h-12 cursor-pointer items-center gap-3">
            <input type="hidden" name="status_aktif" value="0">
            <input type="checkbox" name="status_aktif" value="1"
                class="h-5 w-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                {{ old('status_aktif', $qris ? ($qris->status_aktif ? '1' : '0') : '1') == '1' ? 'checked' : '' }}>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Status aktif
            </span>
        </label>
        <small class="text-gray-400">Hanya satu QRIS yang bisa aktif, mengaktifkan ini otomatis menonaktifkan QRIS lainnya.</small>
    </div>
</div>