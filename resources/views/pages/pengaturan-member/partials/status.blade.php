@php
    $now = now();
    $isKadaluwarsa = $item->selesai_berlaku && $now > $item->selesai_berlaku;
    $selesaiIso = $item->selesai_berlaku ? $item->selesai_berlaku->toIso8601String() : '';
@endphp
<div data-promo-status-cell data-selesai="{{ $selesaiIso }}">
    <span class="promo-expired-badge items-center rounded-full bg-red-100 dark:bg-red-500/10 px-3 py-1 text-xs font-medium text-red-700 dark:text-red-400 {{ $isKadaluwarsa ? 'inline-flex' : 'hidden' }}">Kadaluwarsa</span>
    <form action="{{ route('pengaturan-member.toggle-status', $item->id) }}" method="POST" class="promo-toggle-form {{ $isKadaluwarsa ? 'hidden' : 'inline' }}">
        @csrf
        @method('PATCH')
        <label class="relative inline-flex items-center cursor-pointer" title="Klik untuk mengaktifkan/menonaktifkan">
            <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $item->status_aktif ? 'checked' : '' }}>
            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>
        </label>
    </form>
</div>
