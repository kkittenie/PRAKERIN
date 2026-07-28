@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="p-6 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-xl font-semibold">
                Pengaturan Poin Member
            </h3>
        </div>

        <form action="{{ route('pengaturan-member.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">

                {{-- Status --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Status Sistem Poin
                    </label>

                    <select
                        name="aktif"
                        class="w-full rounded-lg border-gray-300">
                        <option value="1"
                            {{ $pengaturan->aktif ? 'selected' : '' }}>
                            Aktif
                        </option>

                        <option value="0"
                            {{ !$pengaturan->aktif ? 'selected' : '' }}>
                            Nonaktif
                        </option>
                    </select>
                </div>

                {{-- Minimal Belanja --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Minimal Belanja (Rp)
                    </label>

                    <input
                        type="number"
                        name="minimal_belanja"
                        class="w-full rounded-lg border-gray-300"
                        value="{{ old('minimal_belanja', $pengaturan->minimal_belanja) }}">
                </div>

                {{-- Poin --}}
                <div>
                    <label class="block mb-2 font-medium">
                        Poin yang Didapat
                    </label>

                    <input
                        type="number"
                        name="poin"
                        class="w-full rounded-lg border-gray-300"
                        value="{{ old('poin', $pengaturan->poin) }}">
                </div>

                {{-- Preview --}}
                <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">

                    <h4 class="font-semibold mb-2">
                        Preview
                    </h4>

                    <p>
                        Setiap belanja sebesar
                        <b>
                            Rp{{ number_format($pengaturan->minimal_belanja,0,',','.') }}
                        </b>

                        akan mendapatkan

                        <b>{{ $pengaturan->poin }} poin</b>.
                    </p>

                </div>

            </div>

            <div class="border-t border-gray-200 p-6 flex justify-end">

                <button
                    type="submit"
                    class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                    Simpan Pengaturan
                </button>

            </div>

        </form>

    </div>

</div>
@endsection