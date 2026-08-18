<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\ActivityLogHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()
    {
        try {
            return view('pages.auth.signin');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();

            $request->session()->regenerate();

            $user = Auth::user();

            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'user' => $user->name,
                    'email' => $user->email,
                ])
                ->log('Login ke sistem');

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }

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

            return redirect('/');
        } catch (\Throwable $e) {
            return ActivityLogHelper::handleException($e);
        }
    }
}
