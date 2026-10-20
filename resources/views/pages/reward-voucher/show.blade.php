@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Detail Reward Voucher" parentTitle="Reward Voucher" :parentUrl="route('reward-voucher.index')" />

<div class="space-y-6" x-data="{
    tabAktif: 'produk',
    modalTambahMember: false,
    cariMemberModal: '',
    itemsPerPage: '10',
    halamanProduk: 1,
    semuaProduk: @js($voucher->produks->map(function ($p) use ($voucher) {
        $rps = $voucher->rewardProduks->where('produk_id', $p->id);
        $ukurans = $rps->map(fn ($r) => $r->detailProduk?->ukuran ?: $r->varianProduk?->ukuran)->filter()->unique()->values();
        $rasas = $rps->map(fn ($r) => $r->detailProduk?->variasi ?: $r->varianProduk?->variasi)->filter()->unique()->values();

        return [
            'id' => $p->id,
            'nama_produk' => $p->nama_produk,
            'kode_produk' => $p->kode_produk,
            'foto' => $p->foto,
            'harga_jual' => $p->harga_jual,
            'ukurans' => $ukurans,
            'rasas' => $rasas,
        ];
    })->values()),
    get totalHalamanProduk() {
        if (this.itemsPerPage === 'semua') return 1;
        const perPage = parseInt(this.itemsPerPage) || 10;
        return Math.max(1, Math.ceil(this.semuaProduk.length / perPage));
    },
    get produkDitampilkan() {
        if (this.itemsPerPage === 'semua') return this.semuaProduk;
        const perPage = parseInt(this.itemsPerPage) || 10;
        const start = (this.halamanProduk - 1) * perPage;
        return this.semuaProduk.slice(start, start + perPage);
    },
    fotoUrl(foto) {
        if (!foto) return '';
        if (foto.startsWith('http://') || foto.startsWith('https://')) return foto;
        return '/storage/' + foto;
    },
    formatNumber(v) {
        return new Intl.NumberFormat('id-ID').format(v);
    }
}">

    @php
        $statusClass = $voucher->status_aktif
            ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400'
            : 'bg-gray-100 dark:bg-gray-500/10 text-gray-600 dark:text-gray-400';
    @endphp

    <x-common.component-card title="Informasi Voucher">

        <div class="flex items-start justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 shrink-0 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 overflow-hidden flex items-center justify-center">
                    @if($voucher->gambar)
                        <img src="{{ asset('storage/'.$voucher->gambar) }}" class="w-full h-full object-cover" alt="{{ $voucher->nama }}">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    @endif
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $voucher->nama }}</h3>
                    <span class="text-xs font-mono text-gray-500 dark:text-gray-400 block mt-0.5">{{ $voucher->kode_reward }}</span>
                </div>
            </div>
            <span class="shrink-0 px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                {{ $voucher->status_aktif ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="md:col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Deskripsi</p>
                <p class="font-semibold text-gray-800 dark:text-white whitespace-pre-line">
                    {{ $voucher->deskripsi ?: '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Target Member</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    @if($voucher->target_member === 'khusus')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                            Khusus Member Terpilih ({{ $voucher->members->count() }} Member)
                        </span>
                    @elseif($voucher->untukNonMember())
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                            Non Member (Tanpa Member)
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            Umum (Semua Member)
                        </span>
                    @endif
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $voucher->untukNonMember() ? 'Kuota Penggunaan Total' : 'Batas Penggunaan Per Member' }}
                </p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->batas_penggunaan_per_member ?: 1 }} kali pemakaian
                    @if($voucher->untukNonMember())
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">(sisa {{ $voucher->sisaKuotaNonMember() }}x)</span>
                    @endif
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Jenis Potongan</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->jenis_potongan === 'nominal' ? 'Nominal Rupiah (Rp)' : 'Persentase (%)' }}
                </p>
            </div>

            @if($voucher->jenis_potongan === 'nominal')
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nominal Potongan</p>
                    <p class="font-semibold text-gray-800 dark:text-white">
                        Rp {{ number_format($voucher->nominal_potongan, 0, ',', '.') }}
                    </p>
                </div>
            @else
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Persentase Potongan</p>
                    <p class="font-semibold text-gray-800 dark:text-white">
                        {{ rtrim(rtrim(number_format($voucher->persentase, 2, ',', '.'), '0'), ',') }}%
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Maksimal Potongan</p>
                    <p class="font-semibold text-gray-800 dark:text-white">
                        {{ $voucher->maksimal_potongan ? 'Rp ' . number_format($voucher->maksimal_potongan, 0, ',', '.') : 'Tidak dibatasi' }}
                    </p>
                </div>
            @endif

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Minimal Belanja</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->min_pembelian > 0 ? 'Rp ' . number_format($voucher->min_pembelian, 0, ',', '.') : 'Tanpa Minimum' }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Digunakan</p>
                <p class="font-semibold text-brand-600 dark:text-brand-400">
                    {{ $voucher->usages->count() }} kali pemakaian
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Dibuat</p>
                <p class="font-semibold text-gray-800 dark:text-white">
                    {{ $voucher->created_at->format('d-m-Y H:i') }}
                </p>
            </div>

        </div>

    </x-common.component-card>

    {{-- Tabs: Produk Berlaku | Member Khusus | Riwayat Penggunaan --}}
    <x-common.component-card>
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="flex gap-6 -mb-px">
                <button type="button" @click="tabAktif = 'produk'"
                    :class="tabAktif === 'produk' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm transition">
                    Produk Berlaku ({{ $voucher->produks->count() }})
                </button>
                @if($voucher->target_member === 'khusus')
                    <button type="button" @click="tabAktif = 'member'"
                        :class="tabAktif === 'member' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium'"
                        class="py-3 px-1 border-b-2 text-sm transition">
                        Daftar Member Khusus ({{ $voucher->members->count() }})
                    </button>
                @endif
                <button type="button" @click="tabAktif = 'riwayat'"
                    :class="tabAktif === 'riwayat' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm transition">
                    Riwayat Penggunaan ({{ $voucher->usages->count() }})
                </button>
            </nav>
        </div>

        {{-- TAB 1: PRODUK BERLAKU --}}
        <div x-show="tabAktif === 'produk'">
            <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                <h3 class="text-base font-bold text-gray-800 dark:text-white">
                    Produk Yang Diberi Voucher (<span x-text="semuaProduk.length"></span> Produk)
                </h3>
                <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-600 dark:text-gray-400 shrink-0" x-show="semuaProduk.length > 0">
                    <span>Tampilkan:</span>
                    <select x-model="itemsPerPage" @change="halamanProduk = 1"
                        class="bg-transparent font-bold text-amber-600 dark:text-amber-400 focus:outline-none cursor-pointer">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="semua">Semua</option>
                    </select>
                </div>
            </div>

            <div x-show="semuaProduk.length === 0" class="py-10 text-center text-gray-400 dark:text-gray-500 text-sm">
                Belum ada produk yang dipilih.
            </div>

            <div x-show="semuaProduk.length > 0">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <template x-for="produk in produkDitampilkan" :key="produk.id">
                        <div class="flex flex-col bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700/80 rounded-xl p-3">
                            <div class="w-full aspect-square rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-800 mb-3">
                                <template x-if="produk.foto">
                                    <img :src="fotoUrl(produk.foto)" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!produk.foto">
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xxs font-bold uppercase bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 text-amber-700 dark:text-amber-400"
                                        x-text="produk.nama_produk.substring(0,2)"></div>
                                </template>
                            </div>
                            <div class="flex-1 flex flex-col justify-between">
                                <div>
                                    <h4 class="font-bold text-xs text-gray-900 dark:text-white line-clamp-2 leading-tight" x-text="produk.nama_produk"></h4>
                                    <span class="text-xxs text-gray-400 dark:text-gray-500 mt-1 block" x-text="produk.kode_produk"></span>
                                    <template x-if="(produk.ukurans && produk.ukurans.length > 0) || (produk.rasas && produk.rasas.length > 0)">
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            <template x-for="u in produk.ukurans" :key="'sh-u-' + produk.id + '-' + u">
                                                <span class="text-[9px] font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-500/10 px-1.5 py-0.5 rounded-full" x-text="u"></span>
                                            </template>
                                            <template x-for="r in produk.rasas" :key="'sh-r-' + produk.id + '-' + r">
                                                <span class="text-[9px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 rounded-full" x-text="r"></span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                    <span class="text-xs font-extrabold text-brand-600 dark:text-brand-400" x-text="'Rp ' + formatNumber(produk.harga_jual)"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="itemsPerPage !== 'semua' && totalHalamanProduk > 1"
                    class="flex flex-wrap items-center justify-between bg-gray-50 dark:bg-gray-800/40 px-4 py-2.5 rounded-xl border border-gray-100 dark:border-gray-800 mt-4 text-xs gap-2">
                    <span class="text-gray-500 dark:text-gray-400">
                        Menampilkan <span class="font-bold text-gray-800 dark:text-white" x-text="produkDitampilkan.length"></span> dari
                        <span class="font-bold text-gray-800 dark:text-white" x-text="semuaProduk.length"></span> produk
                    </span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="if(halamanProduk > 1) halamanProduk--" :disabled="halamanProduk <= 1"
                            class="px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition text-gray-700 dark:text-gray-300">
                            &laquo; Prev
                        </button>
                        <span class="px-2 font-bold text-amber-600 dark:text-amber-400" x-text="halamanProduk + ' / ' + totalHalamanProduk"></span>
                        <button type="button" @click="if(halamanProduk < totalHalamanProduk) halamanProduk++" :disabled="halamanProduk >= totalHalamanProduk"
                            class="px-3 py-1 rounded-lg border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed font-semibold transition text-gray-700 dark:text-gray-300">
                            Next &raquo;
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: MEMBER KHUSUS (Tambah / Cabut Akses) --}}
        @if($voucher->target_member === 'khusus')
            <div x-show="tabAktif === 'member'">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Daftar Member Berhak</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Hanya member di bawah ini yang dapat melihat dan menggunakan voucher ini.</p>
                    </div>
                    <button type="button" @click="modalTambahMember = true"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-lg text-xs font-bold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Member</span>
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 uppercase font-bold">
                            <tr>
                                <th class="p-3">Kode Member</th>
                                <th class="p-3">Nama Member</th>
                                <th class="p-3">No. HP</th>
                                <th class="p-3 text-center">Pemakaian</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($voucher->members as $mbr)
                                @php
                                    $pemakaian = $voucher->usages->where('member_id', $mbr->id)->count();
                                    $batas = $voucher->batas_penggunaan_per_member ?: 1;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="p-3 font-mono font-bold text-amber-700 dark:text-amber-400">{{ $mbr->kode_member }}</td>
                                    <td class="p-3 font-semibold text-gray-900 dark:text-white">{{ $mbr->nama }}</td>
                                    <td class="p-3 text-gray-500 dark:text-gray-400">{{ $mbr->no_hp }}</td>
                                    <td class="p-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $pemakaian >= $batas ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                            {{ $pemakaian }} / {{ $batas }} kali
                                        </span>
                                    </td>
                                    <td class="p-3 text-right">
                                        <form action="{{ route('reward-voucher.cabut-member', [$voucher->id, $mbr->id]) }}" method="POST"
                                            onsubmit="return confirm('Cabut akses voucher untuk {{ $mbr->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-700 hover:underline">
                                                Cabut Akses
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-gray-400">Belum ada member khusus yang ditambahkan ke voucher ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- TAB 3: RIWAYAT PENGGUNAAN --}}
        <div x-show="tabAktif === 'riwayat'">
            <div class="mb-4">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Riwayat Penggunaan Voucher</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Daftar transaksi yang telah berhasil menggunakan voucher ini.</p>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 uppercase font-bold">
                        <tr>
                            <th class="p-3">Waktu Digunakan</th>
                            <th class="p-3">Kode Member</th>
                            <th class="p-3">Nama Member</th>
                            <th class="p-3">No. Transaksi</th>
                            <th class="p-3 text-right">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($voucher->usages()->with(['member', 'transaksi'])->latest('used_at')->get() as $usage)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="p-3 text-gray-600 dark:text-gray-400">{{ $usage->used_at ? $usage->used_at->translatedFormat('d M Y, H:i') : '-' }}</td>
                                <td class="p-3 font-mono font-bold text-amber-700 dark:text-amber-400">{{ $usage->member?->kode_member ?: '-' }}</td>
                                <td class="p-3 font-semibold text-gray-900 dark:text-white">
                                    @if($usage->member)
                                        {{ $usage->member->nama }}
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Non Member</span>
                                        @if($usage->transaksi?->nama_pembeli && $usage->transaksi->nama_pembeli !== 'Pelanggan Umum')
                                            <span class="block text-[11px] font-normal text-gray-500 dark:text-gray-400 mt-0.5">{{ $usage->transaksi->nama_pembeli }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="p-3 font-mono font-bold text-brand-600 dark:text-brand-400">
                                    {{ $usage->transaksi?->kode_transaksi ?: '-' }}
                                </td>
                                <td class="p-3 text-right font-semibold text-gray-900 dark:text-white">
                                    {{ $usage->transaksi ? 'Rp ' . number_format($usage->transaksi->total_bayar, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-gray-400">Belum ada riwayat penggunaan untuk voucher ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <a href="{{ route('reward-voucher.index') }}"
                class="rounded-lg border border-gray-300 dark:border-gray-700 px-5 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                Kembali
            </a>
            <a href="{{ route('reward-voucher.edit', $voucher->id) }}"
                class="rounded-lg bg-brand-500 px-5 py-2 text-white hover:bg-brand-600">
                Edit Voucher
            </a>
        </div>

    </x-common.component-card>

    {{-- MODAL TAMBAH MEMBER --}}
    @if($voucher->target_member === 'khusus')
        <div x-show="modalTambahMember" x-cloak x-transition
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-[99999] p-4">
            <div @click.away="modalTambahMember = false"
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-gray-100 dark:border-gray-800 flex flex-col max-h-[85vh]">
                <div class="p-4 bg-amber-800 text-white flex items-center justify-between">
                    <h3 class="font-bold text-sm">Tambah Akses Member</h3>
                    <button type="button" @click="modalTambahMember = false" class="text-white hover:text-gray-200 text-lg leading-none">&times;</button>
                </div>
                <form action="{{ route('reward-voucher.tambah-member', $voucher->id) }}" method="POST" class="p-5 flex flex-col flex-1 overflow-hidden"
                    x-data="{
                        semuaOpsiMember: @js($allMembers->whereNotIn('id', $voucher->members->pluck('id'))->map(fn($m) => [
                            'id' => $m->id,
                            'nama' => $m->nama,
                            'kode_member' => $m->kode_member,
                            'no_hp' => $m->no_hp,
                        ])->values()),
                        cariMemberTeks: '',
                        memberDipilih: [],
                        get opsiTersaring() {
                            if (!this.cariMemberTeks.trim()) return this.semuaOpsiMember;
                            const q = this.cariMemberTeks.toLowerCase();
                            return this.semuaOpsiMember.filter(m =>
                                (m.nama && m.nama.toLowerCase().includes(q)) ||
                                (m.no_hp && m.no_hp.toLowerCase().includes(q)) ||
                                (m.kode_member && m.kode_member.toLowerCase().includes(q))
                            );
                        },
                        toggle(id) {
                            const idx = this.memberDipilih.indexOf(id);
                            if (idx > -1) this.memberDipilih.splice(idx, 1);
                            else this.memberDipilih.push(id);
                        }
                    }">
                    @csrf
                    <input type="text" x-model="cariMemberTeks" placeholder="Cari nama, no HP, atau kode member..."
                        class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 text-xs mb-3">

                    <div class="flex-1 overflow-y-auto space-y-1.5 custom-scrollbar min-h-0 pr-1">
                        <template x-for="m in opsiTersaring" :key="m.id">
                            <label class="flex items-center justify-between p-2.5 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 hover:bg-amber-50 dark:hover:bg-amber-950/30 cursor-pointer text-xs">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="member_ids[]" :value="m.id"
                                        :checked="memberDipilih.includes(m.id)"
                                        @change="toggle(m.id)"
                                        class="rounded border-gray-300 text-amber-700 focus:ring-amber-500">
                                    <span class="font-bold text-gray-800 dark:text-white" x-text="m.nama"></span>
                                    <span class="text-gray-400" x-text="m.no_hp"></span>
                                </div>
                                <span class="font-mono text-amber-700 dark:text-amber-400 font-semibold" x-text="m.kode_member"></span>
                            </label>
                        </template>
                        <div x-show="opsiTersaring.length === 0" class="py-6 text-center text-xs text-gray-400">
                            Tidak ada member yang dapat ditambahkan.
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-2">
                        <button type="button" @click="modalTambahMember = false"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-xs font-semibold text-gray-600">Batal</button>
                        <button type="submit" :disabled="memberDipilih.length === 0"
                            class="px-4 py-2 rounded-lg bg-amber-700 hover:bg-amber-800 disabled:opacity-50 text-white text-xs font-bold">
                            Simpan (<span x-text="memberDipilih.length"></span>)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>

@endsection