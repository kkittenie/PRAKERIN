{{-- Form slide welcome, dipakai bareng create & edit. Variabel: $slide (null saat tambah) --}}
@php $slide = $slide ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Judul <span class="text-red-500">*</span></label>
        <input type="text" name="judul" value="{{ old('judul', $slide?->judul) }}" maxlength="150"
            placeholder="Contoh: Fresh Bakes, Sweeter Savings"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        @error('judul') <small class="text-red-500">{{ $message }}</small> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
        <input type="text" name="deskripsi" value="{{ old('deskripsi', $slide?->deskripsi) }}" maxlength="255"
            placeholder="Kalimat singkat di bawah judul"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        @error('deskripsi') <small class="text-red-500">{{ $message }}</small> @enderror
    </div>

    <div class="md:col-span-2" x-data="{ preview: @js($slide?->gambar ? asset('storage/'.$slide->gambar) : null), nama: '' }">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Gambar @if (! $slide) <span class="text-red-500">*</span> @endif
        </label>

        <div class="flex items-start gap-4">
            <div class="flex h-40 w-32 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                <img x-show="preview" :src="preview" alt="Preview slide" class="h-full w-full object-cover">
                <span x-show="! preview" class="px-2 text-center text-xs text-gray-400">Belum ada gambar</span>
            </div>

            <div class="min-w-0 flex-1">
                <label for="input-gambar-slide"
                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                    {{ $slide ? 'Ganti Gambar' : 'Pilih Gambar' }}
                </label>
                <input id="input-gambar-slide" type="file" name="gambar" accept="image/png,image/jpeg,image/webp" class="hidden"
                    @change="const f = $event.target.files[0]; if (f) { nama = f.name; preview = URL.createObjectURL(f); }">
                <p class="mt-2 truncate text-xs text-gray-500 dark:text-gray-400" x-text="nama || '{{ $slide ? 'Kosongkan jika tidak ingin mengganti gambar.' : 'Belum ada gambar dipilih.' }}'"></p>
                <span class="mt-2 block text-xs text-gray-400 dark:text-gray-500">
                    Tampil di halaman welcome mobile (layar penuh di desktop, portrait di HP). Disarankan gambar portrait/persegi
                    minimal 1000px, format jpg/png/webp, maks 4MB.
                </span>
                @error('gambar') <small class="text-red-500">{{ $message }}</small> @enderror
            </div>
        </div>
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan <span class="text-red-500">*</span></label>
        <input type="number" min="0" name="urutan" value="{{ old('urutan', $slide?->urutan ?? 0) }}"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        <small class="text-gray-400">Angka lebih kecil tampil lebih dulu.</small>
        @error('urutan') <small class="block text-red-500">{{ $message }}</small> @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <label class="flex h-12 cursor-pointer items-center gap-3">
            <input type="hidden" name="status_aktif" value="0">
            <input type="checkbox" name="status_aktif" value="1"
                class="h-5 w-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                {{ old('status_aktif', $slide ? ($slide->status_aktif ? '1' : '0') : '1') == '1' ? 'checked' : '' }}>
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">Tampilkan di halaman welcome</span>
        </label>
    </div>
</div>
