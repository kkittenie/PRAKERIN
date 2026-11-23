@if($item->gambar)
    <img src="{{ asset('storage/' . $item->gambar) }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-700 mx-auto">
@else
    <div class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-xs mx-auto">-</div>
@endif
