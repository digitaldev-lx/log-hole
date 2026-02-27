@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between">
        <div class="flex-1 flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Showing <span class="font-medium text-gray-700 dark:text-gray-300">{{ $paginator->firstItem() }}</span>
                to <span class="font-medium text-gray-700 dark:text-gray-300">{{ $paginator->lastItem() }}</span>
                of <span class="font-medium text-gray-700 dark:text-gray-300">{{ $paginator->total() }}</span> results
            </div>

            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span class="px-3 py-1.5 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">&laquo; Prev</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">&laquo; Prev</a>
                @endif

                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-3 py-1.5 text-sm text-gray-400 dark:text-gray-500">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="px-3 py-1.5 text-sm font-medium bg-indigo-600 text-white rounded-md">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">Next &raquo;</a>
                @else
                    <span class="px-3 py-1.5 text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed">Next &raquo;</span>
                @endif
            </div>
        </div>
    </nav>
@endif
