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
        <textarea name="deskripsi" rows="3" maxlength="255"
            placeholder="Kalimat singkat di bawah judul"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">{{ old('deskripsi', $slide?->deskripsi) }}</textarea>
        @error('deskripsi') <small class="text-red-500">{{ $message }}</small> @enderror
    </div>

    <div class="md:col-span-2" x-data="{ preview: @js($slide?->gambar ? asset('storage/'.$slide->gambar) : null), nama: '' }"
         @welcome-slide-cropped.window="preview = $event.detail.url; nama = $event.detail.nama;">
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Gambar @if (! $slide) <span class="text-red-500">*</span> @endif
        </label>

        <div class="flex items-start gap-4">
            <div class="group relative flex h-40 w-32 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                <img id="preview-gambar-slide" x-show="preview" :src="preview" alt="Preview slide" class="h-full w-full object-cover">
                <span x-show="! preview" class="px-2 text-center text-xs text-gray-400">Belum ada gambar</span>

                {{-- Overlay Crop Ulang saat hover gambar --}}
                <div x-show="preview" x-cloak
                    class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
                    onclick="bukaCropDariPreviewWelcomeSlide()" title="Klik untuk crop gambar ini">
                    <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/90 text-gray-800 text-xs font-bold shadow">
                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3v4a1 1 0 001 1h4M7 3H5a1 1 0 00-1 1v14a1 1 0 001 1h14a1 1 0 001-1v-2M7 3l10 10M17 21v-4a1 1 0 00-1-1H7" />
                        </svg>
                        Crop
                    </span>
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <label for="input-gambar-slide"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600">
                        {{ $slide ? 'Ganti Gambar' : 'Pilih Gambar' }}
                    </label>
                    <button type="button" x-show="preview" x-cloak onclick="bukaCropDariPreviewWelcomeSlide()"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3v4a1 1 0 001 1h4M7 3H5a1 1 0 00-1 1v14a1 1 0 001 1h14a1 1 0 001-1v-2M7 3l10 10M17 21v-4a1 1 0 00-1-1H7" />
                        </svg>
                        <span>Crop Gambar</span>
                    </button>
                </div>
                <input id="input-gambar-slide" type="file" name="gambar" accept="image/png,image/jpeg,image/webp" class="hidden"
                    @change="bukaCropWelcomeSlide($event.target)">
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
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan</label>
        <input type="number" min="0" name="urutan" value="{{ old('urutan', $slide?->urutan ?? 0) }}"
            class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
        <small class="text-gray-400">Angka lebih kecil tampil lebih dulu.</small>
        @error('urutan') <small class="block text-red-500">{{ $message }}</small> @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <div class="flex items-center gap-3 h-12">
            <input type="hidden" name="status_aktif" value="0">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="status_aktif" value="1" class="sr-only peer"
                    {{ old('status_aktif', $slide ? ($slide->status_aktif ? '1' : '0') : '1') == '1' ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>
                <span class="ml-3 text-sm font-semibold text-gray-800 dark:text-gray-200">Tampilkan di halaman welcome</span>
            </label>
        </div>
    </div>
</div>
