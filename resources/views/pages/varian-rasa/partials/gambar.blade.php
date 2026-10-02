@if ($item->gambar)
    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->nama_rasa }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700 mx-auto">
@else
    <div class="w-12 h-12 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </div>
@endif