@if($item->punya_ukuran)
    <div class="text-xs font-medium text-gray-500">Besar: <span class="font-bold text-amber-700 dark:text-amber-400 text-sm">Rp {{ number_format($item->harga_besar, 0, ',', '.') }}</span></div>
    <div class="text-xs font-medium text-gray-500">Kecil: <span class="font-bold text-amber-700 dark:text-amber-400 text-sm">Rp {{ number_format($item->harga_kecil, 0, ',', '.') }}</span></div>
@else
    Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
@endif
