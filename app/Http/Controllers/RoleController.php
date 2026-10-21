<?php

namespace App\Http\Controllers;

use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Role::with('permissions')->withCount('permissions');

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addColumn('permission_html', fn ($item) => view('pages.roles.partials.permission', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('pages.roles.partials.aksi', compact('item'))->render())
                    ->rawColumns(['permission_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data role.'], 500);
            }
        }

        $modules = self::daftarModulPermissions();
        $allPermissions = Permission::orderBy('name')->get();

        return view('pages.roles.index', [
            'title' => 'Kelola Role',
            'modules' => $modules,
            'allPermissions' => $allPermissions,
        ]);
    }

    public static function daftarModulPermissions(): array
    {
        return [
            'dashboard' => [
                'nama' => 'Dashboard & Penjualan',
                'deskripsi' => 'Statistik penjualan, omset, grafik harian & bulanan',
                'grup' => 'Dashboard',
            ],
            'transaksi' => [
                'nama' => 'Transaksi Kasir (POS)',
                'deskripsi' => 'Point of Sales kasir, tahan pesanan, proses bayar & cetak',
                'grup' => 'Transaksi',
            ],
            'pesanan_pickup' => [
                'nama' => 'Pesanan Pick Up (Online)',
                'deskripsi' => 'Pesanan masuk dari Member App, konfirmasi, & status ambil',
                'grup' => 'Transaksi',
            ],
            'produk' => [
                'nama' => 'Produk & Varian',
                'deskripsi' => 'Daftar produk, harga jual, ukuran, dan varian rasa',
                'grup' => 'Data Master',
            ],
            'kategori_produk' => [
                'nama' => 'Kategori Produk',
                'deskripsi' => 'Kategori pengelompokan produk makanan & minuman',
                'grup' => 'Data Master',
            ],
            'bundling' => [
                'nama' => 'Paket Bundling',
                'deskripsi' => 'Paket hemat produk dan bundling varian',
                'grup' => 'Data Master',
            ],
            'satuan' => [
                'nama' => 'Satuan Ukuran',
                'deskripsi' => 'Satuan produk dan bahan baku (pcs, gram, kg, dll)',
                'grup' => 'Data Master',
            ],
            'stok' => [
                'nama' => 'Bahan Baku & Stok',
                'deskripsi' => 'Bahan baku, mutasi stok, stok minimum & transaksi bahan',
                'grup' => 'Data Master',
            ],
            'banner' => [
                'nama' => 'Banner Promosi',
                'deskripsi' => 'Banner promosi & iklan pada aplikasi mobile/web',
                'grup' => 'Data Master',
            ],
            'welcome_slide' => [
                'nama' => 'Slide Welcome',
                'deskripsi' => 'Slide pembuka di halaman login / welcome member',
                'grup' => 'Data Master',
            ],
            'qris' => [
                'nama' => 'QRIS Pembayaran',
                'deskripsi' => 'Gambar dan status barcode QRIS pembayaran statis/dinamis',
                'grup' => 'Data Master',
            ],
            'pajak' => [
                'nama' => 'Pengaturan Pajak',
                'deskripsi' => 'Persentase pajak transaksi (PB1 / PPN restoran)',
                'grup' => 'Data Master',
            ],
            'konten_statis' => [
                'nama' => 'Konten Statis',
                'deskripsi' => 'Tentang Kami, Syarat & Ketentuan, Kebijakan Privasi',
                'grup' => 'Data Master',
            ],
            'produksi' => [
                'nama' => 'Produksi Dapur',
                'deskripsi' => 'Antrean produksi, live status, kurangi stok & label dapur',
                'grup' => 'Manajemen Produk',
            ],
            'promo' => [
                'nama' => 'Potongan Harga (Promo)',
                'deskripsi' => 'Diskon potongan harga produk / kategori',
                'grup' => 'Manajemen Produk',
            ],
            'promo_event' => [
                'nama' => 'Promo Event Produk',
                'deskripsi' => 'Promo musiman/event khusus paket dan produk',
                'grup' => 'Manajemen Produk',
            ],
            'member' => [
                'nama' => 'Kelola Member',
                'deskripsi' => 'Data member, poin loyalitas, level badge & pengaturan poin',
                'grup' => 'Member & Reward',
            ],
            'reward' => [
                'nama' => 'Reward & Loyalitas',
                'deskripsi' => 'Program reward stamp, voucher reward & riwayat redeem',
                'grup' => 'Member & Reward',
            ],
            'laporan' => [
                'nama' => 'Laporan & Histori',
                'deskripsi' => 'Laporan penjualan, omset, ekspor data & histori transaksi',
                'grup' => 'Laporan',
            ],
            'notifikasi' => [
                'nama' => 'Notifikasi Sistem',
                'deskripsi' => 'Peringatan pesanan masuk, stok menipis & aktivitas penting',
                'grup' => 'Laporan',
            ],
            'activity_log' => [
                'nama' => 'Activity Log',
                'deskripsi' => 'Audit log riwayat aktivitas admin & kasir',
                'grup' => 'Laporan',
            ],
            'role' => [
                'nama' => 'Kelola Role & Hak Akses',
                'deskripsi' => 'Manajemen peran (roles) dan pembagian permission',
                'grup' => 'Manajemen Akses',
            ],
            'user' => [
                'nama' => 'Kelola Pengguna',
                'deskripsi' => 'Akun login admin, kasir, dan staf sistem',
                'grup' => 'Manajemen Akses',
            ],
            'pegawai' => [
                'nama' => 'Kelola Pegawai',
                'deskripsi' => 'Data pegawai, profil karyawan, dan staf toko',
                'grup' => 'Manajemen Akses',
            ],
        ];
    }

    public function create()
    {
        try {
            if (!auth()->user()->can('tambah_role')) {
                abort(403, 'Anda tidak memiliki hak akses untuk menambah role.');
            }

            $modules = self::daftarModulPermissions();
            $allPermissions = Permission::orderBy('name')->get();

            return view('pages.roles.create', [
                'title' => 'Tambah Role',
                'modules' => $modules,
                'allPermissions' => $allPermissions,
                'rolePermissions' => [],
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('tambah_role')) {
                abort(403, 'Anda tidak memiliki hak akses untuk menambah role.');
            }

            $request->validate([
                'name' => 'required|string|max:100|unique:roles,name',
                'permissions' => 'nullable|array',
            ]);

            $role = Role::create(['name' => trim($request->name)]);
            $role->syncPermissions($request->permissions ?? []);

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'role' => $role->name,
                    'permissions' => $request->permissions ?? [],
                ])
                ->log('menambahkan role');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Role '{$role->name}' berhasil dibuat.",
                ]);
            }

            return redirect()->route('roles.index')->with('success', "Role '{$role->name}' berhasil dibuat.");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function show(string $id)
    {
        return redirect()->route('roles.edit', $id);
    }

    public function edit(string $id)
    {
        try {
            if (!auth()->user()->can('edit_role')) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengedit role.');
            }

            $role = Role::findOrFail($id);
            $modules = self::daftarModulPermissions();
            $allPermissions = Permission::orderBy('name')->get();
            $rolePermissions = $role->permissions->pluck('name')->toArray();

            return view('pages.roles.edit', [
                'title' => 'Edit Role',
                'role' => $role,
                'modules' => $modules,
                'allPermissions' => $allPermissions,
                'rolePermissions' => $rolePermissions,
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            if (!auth()->user()->can('edit_role')) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengedit role.');
            }

            $role = Role::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
                'permissions' => 'nullable|array',
            ]);

            // Lindungi nama role sistem bawaan
            if (!in_array($role->name, ['Admin', 'Kasir', 'Pegawai'])) {
                $role->update(['name' => trim($request->name)]);
            }

            $role->syncPermissions($request->permissions ?? []);

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'role' => $role->name,
                    'permissions' => $request->permissions ?? [],
                ])
                ->log('mengupdate role');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Role '{$role->name}' berhasil diperbarui.",
                ]);
            }

            return redirect()->route('roles.index')->with('success', "Role '{$role->name}' berhasil diperbarui.");
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(string $id)
    {
        try {
            if (!auth()->user()->can('hapus_role')) {
                abort(403, 'Anda tidak memiliki hak akses untuk menghapus role.');
            }

            $role = Role::findOrFail($id);

            if (in_array($role->name, ['Admin', 'Kasir', 'Pegawai'])) {
                return back()->with('error', 'Role bawaan sistem tidak bisa dihapus.');
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($role)
                ->withProperties([
                    'role' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ])
                ->log('menghapus role');

            $role->delete();

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            return back()->with('success', 'Role berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
