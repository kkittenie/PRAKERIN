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
                <tbody></tbody>
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
            serverSide: true,
            ajax: '{{ route('member.index') }}',
            columns: [
                { data: 'nama_html', name: 'nama' },
                { data: 'username', name: 'username' },
                { data: 'no_hp', name: 'no_hp' },
                { data: 'email', name: 'email' },
                { data: 'poin', name: 'poin', className: 'font-bold text-amber-700 dark:text-amber-400 text-sm sm:text-base' },
                { data: 'level_html', name: 'level_html', orderable: true, searchable: false },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
            ],
            emptyText: 'Belum ada member'
        });
    });
</script>
@endpush