@extends('member.layout', ['title' => 'Metode Pembayaran'])

@section('content')

    <style>
        .checkout-sticky-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>

    <div
        class="checkout-sticky-header relative -mx-5 -mt-6 flex h-14 items-center justify-center border-b border-gray-100 px-5 sm:-mx-8 md:-mx-10">
        <a href="{{ $dariAkun ? route('member.akun') : route('member.checkout.index') }}"
            onclick="event.preventDefault(); animateNavigate(this.href, 'back');"
            class="absolute left-5 flex h-8 w-8 items-center justify-center rounded-full text-gray-600 transition active:bg-gray-100">
            <span class="rotate-180">
                @include('member.components.icon-chevron-right', ['class' => 'h-4 w-4'])
            </span>
        </a>

        <h1 class="text-sm font-bold text-gray-900">
            Pilih Metode Pembayaran
        </h1>
    </div>

    <div class="mt-5">

        <form method="POST" action="{{ route('member.checkout.metode-pembayaran') }}" class="space-y-2">
            @csrf
            @if ($dariAkun)
                <input type="hidden" name="from" value="akun">
            @endif

            @foreach ($metodePembayaranList as $metode)

                <button type="submit" name="metode_pembayaran" value="{{ $metode }}"
                    class="flex w-full items-center justify-between rounded-2xl border px-4 py-3.5 text-left transition active:bg-gray-50
                    {{ $metodeDipilih === $metode ? 'border-brand-800 bg-brand-50' : 'border-gray-200 bg-white' }}">

                    <span class="flex items-center gap-3">

                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full
                            {{ $metodeDipilih === $metode ? 'bg-white text-brand-700' : 'bg-gray-50 text-gray-400' }}">

                            @if ($metode === 'QRIS')

                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" />
                                    <rect x="14" y="3" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" />
                                    <rect x="3" y="14" width="7" height="7" rx="1" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M14 14h3v3h-3zM19 14h2M14 19h2M19 17v4" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" />
                                </svg>

                            @else

                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <rect x="2" y="6" width="20" height="13" rx="2" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M2 10h20" stroke="currentColor" stroke-width="1.8" />
                                    <path d="M6 15h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>

                            @endif

                        </span>

                        <span class="text-xs font-bold text-gray-900">
                            {{ $metode }}
                        </span>

                    </span>

                    @if ($metodeDipilih === $metode)

                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-700 text-white">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </span>

                    @endif

                </button>

            @endforeach

        </form>

        <p class="mt-3 text-[11px] text-gray-400">
            Pembayaran tetap dilakukan di kasir saat kamu mengambil pesanan —
            ini cuma preferensi buat ditunjukkan ke kasir.
        </p>

    </div>

@endsection