@if($item->punya_ukuran)
    @php $daftar = $item->daftar_ukuran; @endphp
    @if(count($daftar))
        @foreach($daftar as $u)
            <div class="text-xs font-medium text-gray-500">{{ $u['nama'] }}: <span class="font-bold text-amber-700 dark:text-amber-400 text-sm">Rp {{ number_format($u['harga'], 0, ',', '.') }}</span></div>
        @endforeach
    @else
        <span class="text-xs text-gray-400 italic">Belum ada ukuran</span>
    @endif
@else
    Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
@endif