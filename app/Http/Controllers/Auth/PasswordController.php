<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = $request->user();
        
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'user' => $user->name,
                'email' => $user->email,
            ])
            ->log('memperbarui password');

        return back()->with('status', 'password-updated');
    }
}
