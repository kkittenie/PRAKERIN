@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-2">
        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 font-medium text-center sm:text-left">
            @if (method_exists($paginator, 'total') && $paginator->total())
                Menampilkan <span class="font-bold text-gray-800 dark:text-white">{{ $paginator->count() }}</span> dari <span class="font-bold text-gray-800 dark:text-white">{{ $paginator->total() }}</span> data
            @else
                Halaman <span class="font-bold text-gray-800 dark:text-white">{{ $paginator->currentPage() }}</span>
            @endif
        </div>

        <div class="inline-flex shadow-xs rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 divide-x divide-gray-100 dark:divide-gray-700">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900 cursor-not-allowed">
                    « Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 hover:text-amber-700 transition">
                    « Previous
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 hover:text-amber-700 transition">
                    Next »
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900 cursor-not-allowed">
                    Next »
                </span>
            @endif
        </div>
    </nav>
@endif
