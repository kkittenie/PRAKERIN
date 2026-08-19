@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Member" />

    <div class="sdt-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden"
        x-data="{
            modalHapusTerbuka: false,
            formTerpilih: null,
            namaHapus: '',
            modalResetTerbuka: false,
            formResetTerpilih: null,
            namaReset: '',
            passwordBaruReset: '',
            bukaModalHapus(form, nama) {
                this.formTerpilih = form;
                this.namaHapus = nama;
                this.modalHapusTerbuka = true;
            },
            konfirmasiHapus() {
                if (this.formTerpilih) { this.formTerpilih.submit(); }
                this.modalHapusTerbuka = false;
            },
            bukaModalReset(form, nama, passwordBaru) {
                this.formResetTerpilih = form;
                this.namaReset = nama;
                this.passwordBaruReset = passwordBaru;
                this.modalResetTerbuka = true;
            },
            konfirmasiReset() {
                if (this.formResetTerpilih) { this.formResetTerpilih.submit(); }
                this.modalResetTerbuka = false;
            }
        }">

        {{-- Toolbar Header --}}
        <div class="sdt-toolbar">
            <div class="sdt-toolbar-left">
                <div class="sdt-search">
                    <span class="sdt-search-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </span>
                    <input type="text" class="sdt-search-input" placeholder="Cari member...">
                </div>
                <div class="sdt-pagesize">
                    <span>Tampilkan</span>
                    <select>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <div class="sdt-toolbar-right">
                @can('tambah_member')
                    <a href="{{ route('member.create') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-700 hover:bg-amber-800 text-white px-5 py-2.5 font-semibold text-sm transition shadow-xs">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Tambah Member</span>
                    </a>
                @endcan
            </div>
        </div>

        {{-- Table --}}
        <div class="sdt-table-wrap">
            <table id="memberTable" class="sdt-table">
                <thead>
                    <tr>
                        <th class="py-3.5">Nama</th>
                        <th class="py-3.5">Username</th>
                        <th class="py-3.5">No. HP</th>
                        <th class="py-3.5">Email</th>
                        <th class="py-3.5">Poin</th>
                        <th class="py-3.5">Level</th>
                        <th class="py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        @php
                            $levelClasses = match (strtolower($member->level ?? '')) {
                                'gold' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400',
                                'silver' => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                                'bronze' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
                                default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            };
                        @endphp
                        <tr>
                            <td class="font-semibold text-gray-900 dark:text-white text-sm sm:text-base">{{ $member->nama }}</td>
                            <td class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $member->username ? '@' . $member->username : '-' }}</td>
                            <td class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $member->no_hp }}</td>
                            <td class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $member->email ?: '-' }}</td>
                            <td class="font-bold text-amber-700 dark:text-amber-400 text-sm sm:text-base">{{ number_format($member->poin ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $levelClasses }}">
                                    {{ ucfirst($member->level ?? '-') }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap text-right space-x-2">
                                @can('edit_member')
                                    <a href="{{ route('member.edit', $member) }}" class="text-sm font-semibold text-amber-600 hover:underline dark:text-amber-400">Edit</a>
                                @endcan
                                @can('hapus_member')
                                    <form action="{{ route('member.destroy', $member) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="button" @click="bukaModalHapus($el.closest('form'), @js($member->nama))" class="text-sm font-semibold text-red-500 hover:underline">Hapus</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="sdt-footer">
            <div class="sdt-info"></div>
            <div class="sdt-pagination"></div>
        </div>

        {{-- Modal Reset Password --}}
        <div x-show="modalResetTerbuka" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
            <div @click.away="modalResetTerbuka = false" class="w-full max-w-sm overflow-hidden rounded-xl border border-gray-100 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="p-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-2xl font-bold text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">⟳</div>
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Reset Password Member?</h3>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Password member <span x-text="namaReset" class="font-semibold text-gray-700 dark:text-gray-300"></span> akan dikembalikan ke default:
                        <span x-text="passwordBaruReset" class="font-semibold text-gray-700 dark:text-gray-300"></span> (sama dengan nomor HP).
                    </p>
                </div>
                <div class="flex justify-center gap-3 border-t border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
                    <button type="button" @click="modalResetTerbuka = false" class="rounded-lg bg-gray-200 px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Batal</button>
                    <button type="button" @click="konfirmasiReset()" class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700">Reset Password</button>
                </div>
            </div>
        </div>

        {{-- Modal Hapus --}}
        <div x-show="modalHapusTerbuka" x-cloak x-transition class="fixed inset-0 z-[99999] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs">
            <div @click.away="modalHapusTerbuka = false" class="w-full max-w-sm overflow-hidden rounded-xl border border-gray-100 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="p-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-2xl font-bold text-red-600 dark:bg-red-500/10 dark:text-red-400">⚠</div>
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Hapus Member?</h3>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Member <span x-text="namaHapus" class="font-semibold text-gray-700 dark:text-gray-300"></span> akan dihapus dan tidak dapat dipulihkan kembali.
                    </p>
                </div>
                <div class="flex justify-center gap-3 border-t border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
                    <button type="button" @click="modalHapusTerbuka = false" class="rounded-lg bg-gray-200 px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">Batal</button>
                    <button type="button" @click="konfirmasiHapus()" class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-red-700">Hapus</button>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initSaishoDataTable('memberTable', {
            emptyText: 'Belum ada member',
            columnDefs: [{ orderable: false, targets: -1 }]
        });
    });
</script>
@endpush