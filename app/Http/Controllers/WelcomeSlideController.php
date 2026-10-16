<?php

namespace App\Http\Controllers;

use App\Models\WelcomeSlide;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class WelcomeSlideController extends Controller
{
    private const FOLDER = 'welcome-slide';

    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                // Tanpa orderBy manual: urutan awal diatur dari opsi `order` di JS,
                // supaya klik sort header DataTables tidak kalah dengan ORDER BY bawaan.
                $query = WelcomeSlide::query();

                return DataTables::of($query)
                    ->addColumn('kode_html', fn ($item) => view('pages.welcome-slide.partials.kode', compact('item'))->render())
                    ->addColumn('gambar_html', fn ($item) => view('pages.welcome-slide.partials.gambar', compact('item'))->render())
                    ->editColumn('judul', fn ($item) => view('pages.welcome-slide.partials.judul', compact('item'))->render())
                    ->filterColumn('judul', function ($q, $keyword) {
                        $q->where(fn ($w) => $w->where('judul', 'like', "%{$keyword}%")->orWhere('deskripsi', 'like', "%{$keyword}%"));
                    })
                    ->addColumn('status_html', fn ($item) => view('pages.welcome-slide.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn ($item) => view('pages.welcome-slide.partials.aksi', compact('item'))->render())
                    ->rawColumns(['kode_html', 'gambar_html', 'judul', 'status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data slide welcome.'], 500);
            }
        }

        try {
            return view('pages.welcome-slide.index');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function create()
    {
        try {
            return view('pages.welcome-slide.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate($this->aturan(true));
            unset($data['gambar']);

            // Dibuat dulu supaya dapat id, lalu kode (WLS001, dst) dibentuk dari id
            // dan dipakai sebagai nama folder gambar: welcome-slide/WLS001/namafile.png
            $slide = WelcomeSlide::create($data + ['gambar' => '']);

            $kode = 'WLS' . str_pad((string) $slide->id, 3, '0', STR_PAD_LEFT);
            $slide->update([
                'kode_slide' => $kode,
                'gambar' => $request->file('gambar')->store(self::FOLDER . '/' . $kode, 'public'),
            ]);

            activity()->causedBy(auth()->user())->performedOn($slide)
                ->withProperties(['judul' => $slide->judul])->log('menambahkan slide welcome');

            return redirect()->route('welcome-slide.index')->with('success', 'Slide welcome berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function edit(WelcomeSlide $welcomeSlide)
    {
        try {
            return view('pages.welcome-slide.edit', ['slide' => $welcomeSlide]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request, WelcomeSlide $welcomeSlide)
    {
        try {
            $data = $request->validate($this->aturan(false));

            if ($request->hasFile('gambar')) {
                $kode = $welcomeSlide->kode_slide;
                $this->hapusFile($welcomeSlide->gambar);
                $data['kode_slide'] = $kode;
                $data['gambar'] = $request->file('gambar')->store(self::FOLDER . '/' . $kode, 'public');
            } else {
                unset($data['gambar']);
            }

            $welcomeSlide->update($data);

            activity()->causedBy(auth()->user())->performedOn($welcomeSlide)
                ->withProperties(['judul' => $welcomeSlide->judul])->log('memperbarui slide welcome');

            return redirect()->route('welcome-slide.index')->with('success', 'Slide welcome berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function toggleStatus(WelcomeSlide $welcomeSlide)
    {
        try {
            $welcomeSlide->update(['status_aktif' => !$welcomeSlide->status_aktif]);

            return redirect()->back()->with('success', 'Status slide berhasil diubah.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(WelcomeSlide $welcomeSlide)
    {
        try {
            $this->hapusFile($welcomeSlide->gambar);

            activity()->causedBy(auth()->user())->performedOn($welcomeSlide)
                ->withProperties(['judul' => $welcomeSlide->judul])->log('menghapus slide welcome');

            $welcomeSlide->delete();

            return redirect()->route('welcome-slide.index')->with('success', 'Slide welcome berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    private function aturan(bool $wajibGambar): array
    {
        return [
            'judul' => 'required|string|max:150',
            'deskripsi' => 'nullable|string|max:255',
            'gambar' => ($wajibGambar ? 'required' : 'nullable') . '|image|mimes:jpg,jpeg,png,webp|max:4096',
            'urutan' => 'required|integer|min:0',
            'status_aktif' => 'required|boolean',
        ];
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