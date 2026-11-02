<?php

namespace App\Http\Controllers;

use App\Models\PengaturanJamOperasional;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;

class PengaturanJamOperasionalController extends Controller
{
    public function index()
    {
        try {
            $pengaturan = PengaturanJamOperasional::ambil();

            return view('pages.pengaturan-jam-operasional.index', compact('pengaturan'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'jam_buka' => 'required|date_format:H:i',
                'jam_tutup' => 'required|date_format:H:i|after:jam_buka',
            ], [
                'jam_tutup.after' => 'Jam tutup harus lebih besar dari jam buka.',
            ]);

            $pengaturan = PengaturanJamOperasional::query()->first() ?? new PengaturanJamOperasional();
            $pengaturan->fill($validated)->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($pengaturan)
                ->withProperties($pengaturan->toArray())
                ->log("Mengubah jam operasional menjadi {$pengaturan->jam_buka} - {$pengaturan->jam_tutup}");

            return redirect()
                ->route('pengaturan-jam-operasional.index')
                ->with('success', 'Jam operasional berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}