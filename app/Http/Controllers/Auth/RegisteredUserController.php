<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $adminRoleId = $adminRole ? $adminRole->id : null;

        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Add email domain validation based on role
        if ($request->role_id == 1) {
            // Student role - must have @edu.in email
            $validationRules['email'][] = 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.edu\.in$/';
            $validationRules['enrollment_number'] = ['required', 'string', 'max:50'];
            $validationRules['department'] = ['required', 'string', 'max:100'];
            $validationRules['semester'] = ['required', 'integer', 'min:1', 'max:8'];
        } elseif ($request->role_id == 2 || $request->role_id == 3) {
            // Faculty or Higher Faculty role - must have @ac.in email
            $validationRules['email'][] = 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.ac\.in$/';
        }

        $request->validate($validationRules, [
            'email.regex' => $request->role_id == 1 
                ? 'Students must use an email address ending with @edu.in' 
                : 'Faculty members must use an email address ending with @ac.in',
        ]);

        // Custom Unique Check for active/pending accounts
        $existingEmail = User::where('email', $request->email)
            ->whereIn('account_status', ['active', 'pending'])
            ->first();
        if ($existingEmail) {
            return back()->withErrors(['email' => 'The email has already been taken.'])->withInput();
        }

        if ($request->role_id == 1) {
            $existingEnroll = User::where('enrollment_number', $request->enrollment_number)
                ->whereIn('account_status', ['active', 'pending'])
                ->first();
            if ($existingEnroll) {
                return back()->withErrors(['enrollment_number' => 'The enrollment number has already been taken.'])->withInput();
            }
        }

        // Match against directory record
        $matchedUser = null;
        if ($request->role_id == 1) {
            // Student: Match by enrollment number
            $matchedUser = User::where('enrollment_number', $request->enrollment_number)
                ->where('account_status', 'inactive')
                ->first();
        } else {
            // Faculty/Higher: Match by email
            $matchedUser = User::where('email', $request->email)
                ->where('account_status', 'inactive')
                ->first();
        }

        if ($matchedUser) {
            // Match found -> update the directory record
            $matchedUser->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'account_status' => 'active',
                'status' => 'Active',
            ]);
            
            if ($request->role_id == 1) {
                $matchedUser->update([
                    'department' => $request->department,
                    'semester' => $request->semester,
                ]);
            }
            
            $user = $matchedUser;
        } else {
            // No match found -> create new pending user (except admin who auto-activates)
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role_id' => $request->role_id,
                'password' => Hash::make($request->password),
                'account_status' => $request->role_id == $adminRoleId ? 'active' : 'pending',
                'status' => 'Active',
            ];

            if ($request->role_id == 1) {
                $userData['enrollment_number'] = $request->enrollment_number;
                $userData['department'] = $request->department;
                $userData['semester'] = $request->semester;
            }

            $user = User::create($userData);
        }

        event(new Registered($user));

        if ($user->account_status === 'active') {
            Auth::login($user);
            return redirect(route('dashboard', absolute: false));
        } else {
            return redirect()->route('login')->with('status', 'Your registration is pending admin approval because your details were not found in the master directory.');
        }
    }
}
