@if($item->target_member === 'khusus')
    <div class="flex flex-col items-center gap-0.5">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
            Khusus ({{ $item->members()->count() }} Member)
        </span>
        <span class="text-[10px] text-gray-400 dark:text-gray-500">Maks {{ $item->batas_penggunaan_per_member ?: 1 }}x / member</span>
    </div>
@elseif($item->untukNonMember())
    <div class="flex flex-col items-center gap-0.5">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
            Non Member
        </span>
        <span class="text-[10px] text-gray-400 dark:text-gray-500">Kuota {{ $item->kuotaNonMember() }}x total · sisa {{ $item->sisaKuotaNonMember() }}x</span>
    </div>
@else
    <div class="flex flex-col items-center gap-0.5">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
            Semua Member
        </span>
        <span class="text-[10px] text-gray-400 dark:text-gray-500">Maks {{ $item->batas_penggunaan_per_member ?: 1 }}x / member</span>
    </div>
@endif