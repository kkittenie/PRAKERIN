@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Edit {{ $judul }}" parentTitle="Konten Statis" :parentUrl="route('konten-statis.index')" />

<div class="space-y-6">

    <x-common.component-card title="Edit Konten: {{ $judul }}">

        @if (session('error'))
            <div class="mb-4 flex items-start gap-2 rounded-xl bg-red-50 p-3.5 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-400">
                <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0 mt-0.5"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('konten-statis.update', $slug) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">

                {{-- Judul --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Judul Halaman <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="judul"
                        value="{{ old('judul', $konten->judul ?? $judul) }}"
                        placeholder="Contoh: Help Center"
                        class="h-12 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400"
                        required>
                    @error('judul')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Konten (Rich Text Editor) --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Konten
                    </label>
                    <div class="rounded-lg border border-gray-300 dark:border-gray-700 overflow-hidden">
                        <div id="editor-toolbar" class="flex flex-wrap gap-1 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-2">
                            <button type="button" onclick="execCmd('bold')" title="Bold" class="editor-btn font-bold">B</button>
                            <button type="button" onclick="execCmd('italic')" title="Italic" class="editor-btn italic">I</button>
                            <button type="button" onclick="execCmd('underline')" title="Underline" class="editor-btn underline">U</button>
                            <div class="w-px bg-gray-200 dark:bg-gray-700 mx-1 self-stretch"></div>
                            <button type="button" onclick="execCmd('insertUnorderedList')" title="Bullet List" class="editor-btn">≡</button>
                            <button type="button" onclick="execCmd('insertOrderedList')" title="Numbered List" class="editor-btn">1.</button>
                            <div class="w-px bg-gray-200 dark:bg-gray-700 mx-1 self-stretch"></div>
                            <button type="button" onclick="execHeading('h2')" title="Heading 2" class="editor-btn text-xs">H2</button>
                            <button type="button" onclick="execHeading('h3')" title="Heading 3" class="editor-btn text-xs">H3</button>
                            <div class="w-px bg-gray-200 dark:bg-gray-700 mx-1 self-stretch"></div>
                            <button type="button" onclick="execCmd('formatBlock', 'p')" title="Paragraf Normal" class="editor-btn text-xs">P</button>
                            <button type="button" onclick="execCmd('removeFormat')" title="Hapus Format" class="editor-btn text-xs">✕</button>
                        </div>
                        <div
                            id="konten-editor"
                            contenteditable="true"
                            class="min-h-[400px] p-4 text-gray-900 dark:text-white bg-white dark:bg-gray-900 text-sm leading-relaxed focus:outline-none"
                        >{!! old('konten', $konten->konten ?? '') !!}</div>
                    </div>
                    <input type="hidden" name="konten" id="konten-hidden">
                    @error('konten')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-400">Gunakan toolbar di atas untuk memformat teks. Konten akan ditampilkan langsung di halaman member app.</p>
                </div>

            </div>

            {{-- Tombol Aksi --}}
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('konten-statis.index') }}"
                   class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
                    Batal
                </a>
                <button type="submit" onclick="syncKonten()"
                    class="rounded-lg bg-amber-700 px-5 py-2 text-white hover:bg-amber-800 font-semibold">
                    Simpan Konten
                </button>
            </div>

        </form>

    </x-common.component-card>

</div>

<style>
    .editor-btn {
        @apply px-2.5 py-1 text-sm rounded text-gray-700 dark:text-gray-300 hover:bg-amber-100 dark:hover:bg-amber-500/20 hover:text-amber-800 dark:hover:text-amber-400 transition font-medium;
    }
    #konten-editor h2 { font-size: 1.25rem; font-weight: 700; margin: 0.75rem 0 0.25rem; }
    #konten-editor h3 { font-size: 1rem; font-weight: 700; margin: 0.5rem 0 0.25rem; }
    #konten-editor ul { list-style: disc; padding-left: 1.5rem; margin: 0.5rem 0; }
    #konten-editor ol { list-style: decimal; padding-left: 1.5rem; margin: 0.5rem 0; }
    #konten-editor p { margin: 0.25rem 0; }
    #konten-editor strong { font-weight: 700; }
    #konten-editor em { font-style: italic; }
    #konten-editor u { text-decoration: underline; }
</style>

<script>
    function execCmd(cmd, value = null) {
        document.getElementById('konten-editor').focus();
        document.execCommand(cmd, false, value);
    }

    function execHeading(tag) {
        document.getElementById('konten-editor').focus();
        document.execCommand('formatBlock', false, tag);
    }

    function syncKonten() {
        document.getElementById('konten-hidden').value = document.getElementById('konten-editor').innerHTML;
    }

    // Sync before any submit
    document.querySelector('form').addEventListener('submit', function () {
        syncKonten();
    });
</script>

@endsection
