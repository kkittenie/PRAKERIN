<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\User;
use App\Support\ActivityLogHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

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
                    ->addColumn('status_html', fn($item) => view('admin.pegawai.partials.status', compact('item'))->render())
                    ->addColumn('aksi', fn($item) => view('admin.pegawai.partials.aksi', compact('item'))->render())
                    ->orderColumn('status_html', function ($query, $order) {
                        $query->orderBy('status_aktif', $order);
                    })
                    ->filter(function ($query) use ($request) {
                        $search = trim((string) $request->input('search.value', ''));

                        if ($search !== '') {
                            $query->where(function ($q) use ($search) {
                                $q->where('nama', 'like', "%{$search}%")
                                    ->orWhere('jabatan', 'like', "%{$search}%")
                                    ->orWhere('no_hp', 'like', "%{$search}%")
                                    ->orWhere('alamat', 'like', "%{$search}%")
                                    ->orWhereHas('user', function ($u) use ($search) {
                                        $u->where('email', 'like', "%{$search}%")
                                            ->orWhere('username', 'like', "%{$search}%");
                                    });
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

        return view('admin.pegawai.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $roles = Role::orderBy('name')->pluck('name');
            return view('admin.pegawai.create', compact('roles'));
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
                'jabatan' => 'required|exists:roles,name',
                'tanggal_masuk' => 'required|date',
                'status_aktif' => 'required|boolean',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|min:6',
            ], $this->pesanValidasi());

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
        try {
            $pegawai->load('user.roles');
            return view('admin.pegawai.show', compact('pegawai'));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pegawai $pegawai)
    {
        try {
            $pegawai->load('user');
            $roles = Role::orderBy('name')->pluck('name');
            return view('admin.pegawai.edit', compact('pegawai', 'roles'));
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
                'jabatan' => 'required|exists:roles,name',
                'tanggal_masuk' => 'required|date',
                'status_aktif' => 'required|boolean',
                'username' => 'required|string|max:255|unique:users,username,' . optional($pegawai->user)->id,
                'email' => 'required|email|max:255|unique:users,email,' . optional($pegawai->user)->id,
                'password' => 'nullable|min:6',
            ], $this->pesanValidasi());

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
                    $pegawai->user->email = $request->email;
                    if ($request->filled('password')) {
                        $pegawai->user->password = Hash::make($request->password);
                    }
                    $pegawai->user->save();
                    $pegawai->user->syncRoles([$request->jabatan]);
                }

                activity()
                    ->performedOn($pegawai)
                    ->causedBy(auth()->user())
                    ->withProperties(['nama' => $pegawai->nama, 'jabatan' => $pegawai->jabatan])
                    ->log('memperbarui data pegawai');
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

    private function pesanValidasi(): array
    {
        return [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Format nomor HP tidak valid (hanya angka, +, -, spasi, atau tanda kurung).',
            'alamat.required' => 'Alamat wajib diisi.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.exists' => 'Jabatan yang dipilih tidak valid.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date' => 'Format tanggal masuk tidak valid.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}