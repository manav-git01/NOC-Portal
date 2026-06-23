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

        // Enforce at least Medium strength: 8+ chars, letters + numbers
        $password = $validated['password'];
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return back()->withErrors(['password' => 'Password strength must be at least Medium (8+ characters containing both letters and numbers).'], 'updatePassword');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        // Audit Log
        $roleDisplay = $user->role ? ucfirst($user->role->name) : 'User';
        \App\Models\AuditLog::create([
            'admin_name' => "{$user->name} ({$roleDisplay})",
            'action' => 'Password Changed',
            'target' => "User: {$user->name} ({$user->email}) updated their password",
            'timestamp' => now(),
        ]);

        return back()->with('status', 'password-updated');
    }
}
