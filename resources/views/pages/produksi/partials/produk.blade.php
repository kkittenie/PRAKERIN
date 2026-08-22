@php
    $nama = e($item->produk->nama_produk ?? '-');
    $perlu = $item->perlu_tindakan;
    $isKad = $item->is_kadaluarsa;
    $sisa = $item->sisa_belum_disesuaikan;
@endphp
<span class="font-medium text-gray-800 dark:text-white">{{ $nama }}<span class="produksi-badge-tindakan items-center ml-2 rounded-full bg-red-100 dark:bg-red-500/20 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:text-red-400 {{ $perlu ? 'inline-flex' : 'hidden' }}">Kadaluwarsa · sisa {{ $sisa }} pcs</span><span class="produksi-badge-plain items-center ml-2 rounded-full bg-red-100 dark:bg-red-500/20 px-2 py-0.5 text-[10px] font-bold text-red-700 dark:text-red-400 {{ ($isKad && !$perlu) ? 'inline-flex' : 'hidden' }}">Kadaluwarsa</span></span>
