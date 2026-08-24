@can('edit_pajak')
    <form action="{{ route('pengaturan-pajak.toggle-status', $item->id) }}" method="POST" class="inline">
        @csrf @method('PATCH')
        <label class="relative inline-flex items-center cursor-pointer" title="Klik untuk mengaktifkan/menonaktifkan pajak">
            <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $item->status_aktif ? 'checked' : '' }}>
            <div class="w-11 h-6 bg-gray-200 dark:bg-gray-700 rounded-full peer peer-checked:bg-green-600 transition-colors duration-200 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-sm after:transition-all after:duration-200 peer-checked:after:translate-x-5"></div>
        </label>
    </form>
@else
    <span class="text-xs font-semibold {{ $item->status_aktif ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
        {{ $item->status_aktif ? 'Aktif' : 'Nonaktif' }}
    </span>
@endcan
