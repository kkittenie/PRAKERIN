@if($item->tanggal_mulai_tampil || $item->tanggal_selesai_tampil)
    @php
        $mulai = $item->tanggal_mulai_tampil ? $item->tanggal_mulai_tampil->format('d/m/Y') : '-';
        $selesai = $item->tanggal_selesai_tampil ? $item->tanggal_selesai_tampil->format('d/m/Y') : '-';
    @endphp
    <span class="text-xs text-gray-500">{{ $mulai }} &ndash; {{ $selesai }}</span>
@else
    <span class="text-xs text-gray-500 italic">Selamanya</span>
@endif
