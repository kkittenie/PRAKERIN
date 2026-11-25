<div class="inline-flex items-center gap-0.5 justify-end">
    @can('edit_welcome_slide')
        <a href="{{ route('welcome-slide.edit', $item->id) }}" title="Edit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-amber-600 transition hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-500/10">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </a>
    @endcan
    @can('hapus_welcome_slide')
        <form action="{{ route('welcome-slide.destroy', $item->id) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="button" title="Hapus" @click="bukaModalHapus($el.closest('form'), @js($item->judul))" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-[19px] h-[19px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </form>
    @endcan
</div>