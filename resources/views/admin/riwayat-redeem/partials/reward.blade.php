<span class="block font-medium text-gray-800 dark:text-gray-200 text-xs sm:text-sm">{{ $item->reward->nama ?? 'Reward tidak ditemukan' }}</span>
@if($item->reward)
    <span class="inline-flex mt-0.5 items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
        {{ $item->reward->jenis === 'voucher' ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400' }}">
        {{ ucfirst($item->reward->jenis) }}
    </span>
@endif
