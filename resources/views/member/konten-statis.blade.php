@extends('member.layout', ['title' => $judulHalaman])

@section('content')

    {{-- Header --}}
    <div class="-mt-6 flex flex-none items-center justify-between px-0 pt-6 pb-4">
        <a href="{{ route('member.akun') }}" onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
            class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 text-brand-800 transition hover:bg-gray-50 active:scale-95">
            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <h1 class="text-base font-extrabold text-gray-900">{{ $judulHalaman }}</h1>
        <span class="w-9"></span>
    </div>

    {{-- Content Body --}}
    @if ($konten && $konten->konten)
        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed space-y-4 pb-8">
            {!! $konten->konten !!}
        </div>
    @else
        {{-- Empty state --}}
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100">
                @if ($slug === 'help-center')
                    <svg viewBox="0 0 24 24" fill="none" class="h-8 w-8 text-gray-400">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <circle cx="12" cy="17" r="0.5" fill="currentColor" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                @elseif ($slug === 'terms-conditions')
                    <svg viewBox="0 0 24 24" fill="none" class="h-8 w-8 text-gray-400">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.8"/>
                        <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                @else
                    <svg viewBox="0 0 24 24" fill="none" class="h-8 w-8 text-gray-400">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                @endif
            </div>
            <p class="text-sm font-semibold text-gray-500">Konten belum tersedia</p>
            <p class="mt-1 text-xs text-gray-400">Halaman ini sedang dalam proses pengisian.</p>
        </div>
    @endif

@endsection
