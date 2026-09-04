<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        try {
            $user = Auth::user();
            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'user' => $user->name,
                    'email' => $user->email,
                ])
                ->log('Logout dari sistem');

            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect()->route('member.login');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}