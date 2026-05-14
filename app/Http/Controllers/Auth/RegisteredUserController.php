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
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Add email domain validation based on role
        if ($request->role_id == 1) {
            // Student role - must have @edu.in email
            $validationRules['email'][] = 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.edu\.in$/';
            $validationRules['enrollment_number'] = ['required', 'string', 'max:50', 'unique:users'];
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

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
        ];

        // Add student-specific fields if role is student
        if ($request->role_id == 1) {
            $userData['enrollment_number'] = $request->enrollment_number;
            $userData['department'] = $request->department;
            $userData['semester'] = $request->semester;
        }

        $user = User::create($userData);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
