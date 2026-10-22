@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-2">

        {{-- Mobile: Previous / Next only --}}
        <div class="flex gap-2 items-center sm:hidden w-full">
            @if ($paginator->onFirstPage())
                <span class="flex-1 inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-[#B6ADA3] bg-white border border-[#B6ADA3]/30 rounded-xl cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-[#5F6F5B] bg-white border border-[#B6ADA3]/40 rounded-xl hover:bg-[#FAF7F2] transition-colors">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-[#5F6F5B] bg-white border border-[#B6ADA3]/40 rounded-xl hover:bg-[#FAF7F2] transition-colors">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="flex-1 inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-[#B6ADA3] bg-white border border-[#B6ADA3]/30 rounded-xl cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between gap-4">
            <p class="text-xs text-[#5F6F5B]">
                @if ($paginator->firstItem())
                    Menampilkan
                    <span class="font-semibold text-[#2D372E]">{{ $paginator->firstItem() }}</span>
                    –
                    <span class="font-semibold text-[#2D372E]">{{ $paginator->lastItem() }}</span>
                    dari
                    <span class="font-semibold text-[#2D372E]">{{ $paginator->total() }}</span>
                    hasil
                @else
                    {{ $paginator->count() }} hasil
                @endif
            </p>

            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-[#B6ADA3]/30 bg-white text-[#B6ADA3] cursor-not-allowed" aria-hidden="true">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                        aria-label="{{ __('pagination.previous') }}"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-[#B6ADA3]/40 bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] hover:border-[#D8A7B1] transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-medium text-[#B6ADA3]" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs font-bold bg-[#8B3A47] text-white border border-[#8B3A47] shadow-sm">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                    class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs font-medium text-[#5F6F5B] bg-white border border-[#B6ADA3]/40 hover:bg-[#FAF7F2] hover:border-[#D8A7B1] transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                        aria-label="{{ __('pagination.next') }}"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-[#B6ADA3]/40 bg-white text-[#5F6F5B] hover:bg-[#FAF7F2] hover:border-[#D8A7B1] transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-[#B6ADA3]/30 bg-white text-[#B6ADA3] cursor-not-allowed" aria-hidden="true">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
