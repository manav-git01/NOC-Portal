<?php
namespace App\Http\Controllers;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the settings edit form.
     */
    public function editSettings(Request $request): View
    {
        return view('profile.settings', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the dedicated change password form.
     */
    public function editPassword(Request $request): View
    {
        return view('profile.change-password', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update settings profile info.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ];

        if ($user->isStudent()) {
            $rules['department'] = ['required', 'string', 'max:255'];
            $rules['semester'] = ['required', 'integer', 'min:1', 'max:10'];
            $rules['phone'] = [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if ($value === 'N/A' || $value === 'n/a') {
                        $fail('The phone number must be a valid number and cannot be N/A.');
                    }
                }
            ];
        } elseif ($user->isFaculty() || $user->isHigherFaculty()) {
            $rules['department'] = ['required', 'string', 'max:255'];
        } else {
            $rules['department'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->department = $validated['department'] ?? null;

        if ($user->isStudent()) {
            $user->semester = $validated['semester'];
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->remove_photo == '1') {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
        } elseif ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        $user->save();

        // Audit Log
        $roleDisplay = $user->role ? ucfirst($user->role->name) : 'User';
        \App\Models\AuditLog::create([
            'admin_name' => "{$user->name} ({$roleDisplay})",
            'action' => 'Profile Updated',
            'target' => "User: {$user->name} ({$user->email}) updated their profile details",
            'timestamp' => now(),
        ]);

        return redirect()->route('profile.settings')->with('success', 'Profile updated successfully.');
    }
}
