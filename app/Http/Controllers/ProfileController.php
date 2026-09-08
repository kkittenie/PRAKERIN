<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\ActivityLogHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        try {
            return view('pages.profile-edit', [
                'user' => $request->user(),
            ]);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function update(ProfileUpdateRequest $request)
    {
        try {
            $request->user()->fill($request->validated());

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $request->user()->save();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($request->user())
                ->withProperties([
                    'user' => $request->user()->name,
                    'email' => $request->user()->email,
                ])
                ->log('memperbarui profil');

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        try {
            $user->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->withErrors([
                    'password' => 'Akun tidak bisa dihapus karena masih terkait dengan data lain (mis. riwayat produksi, data pegawai, atau transaksi). Lepas/pindahkan dulu keterkaitannya sebelum menghapus akun ini.',
                ], 'userDeletion');
            }

            return ActivityLogHelper::handleException($e);
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }

        activity()
            ->causedBy($user)
            ->withProperties([
                'user' => $user->name,
                'email' => $user->email,
            ])
            ->log('menghapus akun');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}