@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Pengaturan Member" />

<div class="space-y-6">

    <x-common.component-card title="Aturan Poin Member">

        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center mb-6">

            <form method="GET" action="{{ route('pengaturan-member.index') }}" class="w-full sm:w-auto">
                <div class="relative w-full">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari aturan..."
                        class="w-full sm:w-80 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white py-2.5 pl-10 pr-4 focus:border-brand-500 focus:ring-brand-500 placeholder-gray-400 text-sm">

                    <span class="absolute left-3 top-3 text-gray-400">
                        🔍
                    </span>
                </div>
            </form>

            <a href="{{ route('pengaturan-member.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition">
                + Tambah Aturan
            </a>

        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">

                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                        <th class="py-3 px-3 text-left">Nama Aturan</th>
                        <th class="py-3 px-3 text-center">Belanja</th>
                        <th class="py-3 px-3 text-center">Poin</th>
                        <th class="py-3 px-3 text-center">Mulai</th>
                        <th class="py-3 px-3 text-center">Selesai</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                    @forelse($pengaturanMember as $item)

                        @php
                            $now = now();

                            if (!$item->status_aktif) {
                                $status = 'Nonaktif';
                                $badge = 'bg-gray-100 text-gray-600';
                            } elseif ($now < $item->mulai_berlaku) {
                                $status = 'Belum Mulai';
                                $badge = 'bg-yellow-100 text-yellow-700';
                            } elseif ($item->selesai_berlaku && $now > $item->selesai_berlaku) {
                                $status = 'Kadaluarsa';
                                $badge = 'bg-red-100 text-red-700';
                            } else {
                                $status = 'status_aktif';
                                $badge = 'bg-green-100 text-green-700';
                            }
                        @endphp

                        <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-950/20 transition">

                            <td class="py-4 px-3 font-semibold text-gray-900 dark:text-white">
                                {{ $item->nama_aturan }}
                            </td>

                            <td class="py-4 px-3 text-center">
                                Rp {{ number_format($item->minimal_belanja,0,',','.') }}
                            </td>

                            <td class="py-4 px-3 text-center font-bold text-brand-600">
                                {{ $item->poin }} poin
                            </td>

                            <td class="py-4 px-3 text-center text-xs">
                                {{ $item->mulai_berlaku ? $item->mulai_berlaku->format('d M Y H:i') : '-' }}
                            </td>

                            <td class="py-4 px-3 text-center text-xs">
                                {{ $item->selesai_berlaku ? $item->selesai_berlaku->format('d M Y H:i') : 'Selamanya' }}
                            </td>

                            <td class="py-4 px-3 text-center">
                                <form action="{{ route('pengaturan-member.toggle-status',$item->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                        {{ $status }}
                                    </button>
                                </form>
                            </td>

                            <td class="py-4 px-3 text-right space-x-2">

                                <a href="{{ route('pengaturan-member.edit',$item->id) }}"
                                   class="text-amber-600 font-semibold hover:underline">
                                    Edit
                                </a>

                                <form action="{{ route('pengaturan-member.destroy',$item->id) }}"
                                      method="POST"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')

                                    <button onclick="return confirm('Yakin hapus aturan ini?')"
                                            class="text-red-500 font-semibold hover:underline">
                                        Hapus
                                    </button>
                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-500">
                                Belum ada aturan poin
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="mt-6">
            {{ $pengaturanMember->withQueryString()->links() }}
        </div>

    </x-common.component-card>

</div>

@endsection