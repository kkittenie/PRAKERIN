@php
    $fotoList = $item->foto
        ? asset('storage/' . $item->foto)
        : optional($item->detailProduks->first(fn ($d) => $d->foto))->foto_url;
@endphp
@if($fotoList)
    <img src="{{ $fotoList }}" class="w-11 h-11 aspect-square rounded-xl object-cover border mx-auto shadow-xs shrink-0">
@else
    <div class="w-11 h-11 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-sm font-bold mx-auto bg-gray-50 dark:bg-gray-800">-</div>
@endif