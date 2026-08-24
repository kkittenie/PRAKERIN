@if($item->punya_ukuran)
    @php $daftar = $item->daftar_ukuran; @endphp
    @if(count($daftar))
        <div class="flex flex-col items-center gap-1">
            @foreach($daftar as $u)
                @php
                    $stokClass = $u['stok'] <= $u['stok_minimum'] ? 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' : 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
                @endphp
                <div class="whitespace-nowrap"><span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mr-1">{{ $u['nama'] }}:</span><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $stokClass }}">{{ $u['stok'] }}</span></div>
            @endforeach
        </div>
    @else
        <span class="text-xs text-gray-400 italic">Belum ada ukuran</span>
    @endif
@else
    @php
        $stokClass = $item->stok <= $item->stok_minimum ? 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400' : 'bg-green-100 dark:bg-green-500/10 text-green-700 dark:text-green-400';
    @endphp
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $stokClass }}">{{ $item->stok }}</span>
    <div class="text-xs text-gray-400 mt-0.5 font-medium">Min {{ $item->stok_minimum }}</div>
@endif