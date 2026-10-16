<?php

namespace App\Http\Controllers;

use App\Models\QrisImage;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class QrisController extends Controller
{
    private const FOLDER = 'qris';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = QrisImage::query();

                // Urutan awal = urutan input (id), BUKAN status aktif, jadi QRIS yang
                // dinonaktifkan/diaktifkan tidak loncat ke atas. orderBy ini hanya dipasang
                // kalau belum ada sort dari header DataTables, supaya klik sort tidak kalah.
                if (! $request->has('order')) {
                    $query->orderBy('id');
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('kode_html', fn ($item) => view('pages.qris.partials.kode', compact('item'))->render())
                    ->addColumn('gambar_html', fn ($item) => view('pages.qris.partials.gambar', compact('item'))->render())
                    ->editColumn('nama', fn ($item) => view('pages.qris.partials.nama', compact('item'))->render())
                    ->addColumn('status_html', fn ($item) => view('pages.qris.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('pages.qris.partials.aksi', compact('item'))->render())
                    ->rawColumns(['kode_html', 'gambar_html', 'nama', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data QRIS.'], 500);
            }
        }

        try {
            return view('pages.qris.index');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            return view('pages.qris.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate($this->aturan(true));

            $qris = DB::transaction(function () use ($request, $data) {
                // Dibuat dulu supaya dapat id, lalu kode (QRS001, dst) dibentuk dari id
                // dan dipakai sebagai nama folder gambar: qris/QRS001/namafile.png
                $qris = QrisImage::create([
                    'nama' => $data['nama'],
                    'gambar' => '',
                    'status_aktif' => false,
                ]);

                $kode = 'QRS' . str_pad((string) $qris->id, 3, '0', STR_PAD_LEFT);
                $qris->update([
                    'kode_qris' => $kode,
                    'gambar' => $request->file('gambar')->store(self::FOLDER . '/' . $kode, 'public'),
                ]);

                // QRIS pertama otomatis aktif.
                if ($data['status_aktif'] || QrisImage::count() === 1) {
                    $this->jadikanAktif($qris);
                }

                return $qris;
            });

            activity()->causedBy(auth()->user())->performedOn($qris)
                ->withProperties(['nama' => $qris->nama])->log('menambahkan QRIS pembayaran');

            return redirect()->route('qris.index')->with('success', 'Gambar QRIS berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(QrisImage $qris)
    {
        try {
            return view('pages.qris.edit', compact('qris'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, QrisImage $qris)
    {
        try {
            $data = $request->validate($this->aturan(false));

            DB::transaction(function () use ($request, $data, $qris) {
                $perubahan = ['nama' => $data['nama']];

                if ($request->hasFile('gambar')) {
                    $kode = $qris->kode_qris;
                    $this->hapusFile($qris->gambar);
                    $perubahan['kode_qris'] = $kode;
                    $perubahan['gambar'] = $request->file('gambar')->store(self::FOLDER . '/' . $kode, 'public');
                }

                $qris->update($perubahan);

                if ($data['status_aktif']) {
                    $this->jadikanAktif($qris);
                } else {
                    $qris->update(['status_aktif' => false]);
                }
            });

            activity()->causedBy(auth()->user())->performedOn($qris)
                ->withProperties(['nama' => $qris->nama])->log('memperbarui QRIS pembayaran');

            return redirect()->route('qris.index')->with('success', 'Gambar QRIS berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus(QrisImage $qris)
    {
        try {
            $akanAktif = ! $qris->status_aktif;

            DB::transaction(function () use ($qris, $akanAktif) {
                if ($akanAktif) {
                    // Hanya satu QRIS yang aktif: yang lain dinonaktifkan.
                    $this->jadikanAktif($qris);
                } else {
                    $qris->update(['status_aktif' => false]);
                }

                activity()->causedBy(auth()->user())->performedOn($qris)
                    ->withProperties(['nama' => $qris->nama, 'status_aktif' => $akanAktif])
                    ->log('mengubah status QRIS pembayaran');
            });

            return back()->with('success', $akanAktif
                ? 'QRIS diaktifkan. QRIS aktif lainnya dinonaktifkan.'
                : 'QRIS dinonaktifkan. Halaman pembayaran memakai gambar placeholder.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(QrisImage $qris)
    {
        try {
            $this->hapusFile($qris->gambar);

            activity()->causedBy(auth()->user())->performedOn($qris)
                ->withProperties(['nama' => $qris->nama])->log('menghapus QRIS pembayaran');

            $qris->delete();

            return redirect()->route('qris.index')->with('success', 'Gambar QRIS berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    private function aturan(bool $wajibGambar): array
    {
        return [
            'nama' => 'required|string|max:100',
            'gambar' => ($wajibGambar ? 'required' : 'nullable') . '|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status_aktif' => 'required|boolean',
        ];
    }

    /** Hanya satu QRIS yang aktif: yang lain dinonaktifkan. */
    private function jadikanAktif(QrisImage $qris): void
    {
        QrisImage::where('id', '!=', $qris->id)->update(['status_aktif' => false]);
        $qris->update(['status_aktif' => true]);
    }

    /** Hapus file gambar; kalau folder kodenya jadi kosong, foldernya ikut dihapus. */
    private function hapusFile(?string $path): void
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return;
        }

        Storage::disk('public')->delete($path);

        $folder = dirname($path);
        if ($folder !== self::FOLDER && $folder !== '.' && empty(Storage::disk('public')->allFiles($folder))) {
            Storage::disk('public')->deleteDirectory($folder);
        }
    }
}