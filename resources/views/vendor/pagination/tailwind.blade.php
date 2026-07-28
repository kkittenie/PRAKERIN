@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-2">
        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 font-medium text-center sm:text-left">
            @if ($paginator->total())
                Menampilkan <span class="font-bold text-gray-800 dark:text-white">{{ $paginator->count() }}</span> dari <span class="font-bold text-gray-800 dark:text-white">{{ $paginator->total() }}</span> data
            @else
                Total <span class="font-bold text-gray-800 dark:text-white">{{ $paginator->count() }}</span> data
            @endif
        </div>

        <div>
            <span class="inline-flex shadow-xs rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 divide-x divide-gray-100 dark:divide-gray-700">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <span class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900 cursor-not-allowed" aria-hidden="true">
                            « Previous
                        </span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 hover:text-amber-700 transition" aria-label="{{ __('pagination.previous') }}">
                        « Previous
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true">
                            <span class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900 cursor-default">{{ $element }}</span>
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">
                                    <span class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-white bg-amber-600 dark:bg-amber-500 cursor-default">{{ $page }}</span>
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 hover:text-amber-700 transition" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 hover:text-amber-700 transition" aria-label="{{ __('pagination.next') }}">
                        Next »
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <span class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-900 cursor-not-allowed" aria-hidden="true">
                            Next »
                        </span>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
