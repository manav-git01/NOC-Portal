<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    /**
     * Show the mandatory password change form.
     */
    public function showChangeForm(): View
    {
        return view('auth.change-password');
    }

    /**
     * Handle the password change request.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $password = $request->password;
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return back()->withErrors(['password' => 'Password strength must be at least Medium (8+ characters containing both letters and numbers).']);
        }

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        // Audit Log
        $roleDisplay = $user->role ? ucfirst($user->role->name) : 'User';
        \App\Models\AuditLog::create([
            'admin_name' => "{$user->name} ({$roleDisplay})",
            'action' => 'Password Changed',
            'target' => "User: {$user->name} ({$user->email}) completed forced password change on first login",
            'timestamp' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Password changed successfully! Welcome to your dashboard.');
    }
}
