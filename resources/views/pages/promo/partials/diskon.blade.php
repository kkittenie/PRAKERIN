@if($item->jenis_potongan === 'nominal')
    Rp {{ number_format($item->nominal_potongan, 0, ',', '.') }}
@else
    @php
        $persentaseFormatted = rtrim(rtrim(number_format($item->persentase, 2, ',', '.'), '0'), ',');
    @endphp
    {{ $persentaseFormatted }}%
@endif
