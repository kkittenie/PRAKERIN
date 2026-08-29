@if($item->foto)
    <img src="{{ asset('storage/' . $item->foto) }}" class="w-11 h-11 aspect-square rounded-full object-cover border mx-auto shadow-xs shrink-0">
@else
    <div class="w-11 h-11 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-sm font-bold mx-auto bg-gray-50 dark:bg-gray-800 uppercase">
        {{ Str::substr($item->nama, 0, 1) }}
    </div>
@endif
