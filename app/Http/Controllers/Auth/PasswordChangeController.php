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
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

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

        if ($user->isStudent() && ($user->phone === 'N/A' || empty($user->phone) || empty($user->department) || empty($user->semester))) {
            return redirect()->route('profile.settings')
                ->with('success', 'Password changed successfully! Please complete your details (department, semester, and phone number) to access your dashboard.');
        }

        return redirect()->route('dashboard')
            ->with('success', 'Password changed successfully! Welcome to your dashboard.');
    }
}
