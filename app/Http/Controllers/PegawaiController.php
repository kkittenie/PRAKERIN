<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\User;
use App\Support\ActivityLogHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Pegawai::query();

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('tanggal_masuk', fn($item) => \Carbon\Carbon::parse($item->tanggal_masuk)->format('d-m-Y'))
                    ->addColumn('status_html', fn($item) => view('pages.pegawai.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('pages.pegawai.partials.aksi', compact('item'))->render())
                    ->orderColumn('status_html', function ($query, $order) {
                        $query->orderBy('status_aktif', $order);
                    })
                    ->filter(function ($query) use ($request) {
                        // Override pencarian global supaya tetap bisa cari lewat
                        // nama, jabatan, dan no_hp (seperti sebelumnya).
                        $search = $request->input('search.value');

                        if ($search) {
                            $query->where(function ($q) use ($search) {
                                $q->where('nama', 'like', "%{$search}%")
                                    ->orWhere('jabatan', 'like', "%{$search}%")
                                    ->orWhere('no_hp', 'like', "%{$search}%");
                            });
                        }
                    }, true)
                    ->rawColumns(['status_html', 'aksi'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat data pegawai.'], 500);
            }
        }

        return view('pages.pegawai.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('pages.pegawai.create');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:100',
                'no_hp' => 'required|regex:/^[0-9+\-\s()]+$/|max:20',
                'alamat' => 'required|string',
                'jabatan' => 'required|in:Admin,Kasir,Pegawai',
                'tanggal_masuk' => 'required|date',
                'status_aktif' => 'required|boolean',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|nullable|email|unique:users,email',
                'password' => 'required|min:6',
            ]);

            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name' => $request->nama,
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
                $user->assignRole($request->jabatan);

                Pegawai::create([
                    'nama' => $request->nama,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'jabatan' => $request->jabatan,
                    'tanggal_masuk' => $request->tanggal_masuk,
                    'status_aktif' => $request->status_aktif,
                    'user_id' => $user->id,
                ]);

                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->withProperties(['username' => $user->username, 'nama' => $user->name])
                    ->log('menambahkan pegawai & akun');
            });

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Data pegawai & akun login berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Pegawai $pegawai)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pegawai $pegawai)
    {
        try {
            $pegawai->load('user');
            return view('pages.pegawai.edit', compact('pegawai'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, Pegawai $pegawai)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:100',
                'no_hp' => 'required|regex:/^[0-9+\-\s()]+$/|max:20',
                'alamat' => 'required|string',
                'jabatan' => 'required|in:Admin,Kasir,Pegawai',
                'tanggal_masuk' => 'required|date',
                'status_aktif' => 'required|boolean',
                'username' => 'required|string|max:255|unique:users,username,' . optional($pegawai->user)->id,
                'email' => 'required|nullable|email|unique:users,email,' . optional($pegawai->user)->id,
                'password' => 'nullable|min:6',
            ]);

            DB::transaction(function () use ($request, $pegawai) {
                $pegawai->update([
                    'nama' => $request->nama,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'jabatan' => $request->jabatan,
                    'tanggal_masuk' => $request->tanggal_masuk,
                    'status_aktif' => $request->status_aktif,
                ]);

                if ($pegawai->user) {
                    $pegawai->user->name = $request->nama;
                    $pegawai->user->username = $request->username;
                    if ($request->filled('password')) {
                        $pegawai->user->password = Hash::make($request->password);
                    }
                    $pegawai->user->save();
                    $pegawai->user->syncRoles([$request->jabatan]);
                }
            });

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Pegawai $pegawai)
    {
        try {
            $pegawai->delete();

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil dihapus.');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}