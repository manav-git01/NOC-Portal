<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ForgotPasswordController extends Controller
{
    /**
     * Displays the page where users enter their email.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Validates email, generates 6-digit OTP, stores in DB, sends mail.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No user account found with this email address.',
        ]);

        $email = $request->email;
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store hashed OTP in database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Send OTP via email
        Mail::to($email)->send(new OtpMail($otp));

        // Save email in session
        session(['reset_email' => $email]);

        return redirect()->route('password.verify')
            ->with('status', 'A 6-digit OTP has been sent to your email address.');
    }

    /**
     * Displays the page where users enter the 6-digit OTP code.
     */
    public function showVerifyForm(): View|RedirectResponse
    {
        if (!session()->has('reset_email')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Please enter your email address first.']);
        }

        return view('auth.verify-otp');
    }

    /**
     * Validates that the code exists, is associated with the email, and is not expired.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Please enter your email address first.']);
        }

        $tokenRecord = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$tokenRecord) {
            return back()->withErrors(['otp' => 'No OTP request found for this email. Please request a new code.']);
        }

        // Check if OTP is expired (10 minutes validity)
        if (\Carbon\Carbon::parse($tokenRecord->created_at)->addMinutes(10)->isPast()) {
            return back()->withErrors(['otp' => 'This OTP has expired. Please request a new one.']);
        }

        if (!Hash::check($request->otp, $tokenRecord->token)) {
            return back()->withErrors(['otp' => 'The entered OTP code is incorrect.']);
        }

        // OTP is valid. Store verification flag in session
        session(['otp_verified' => true]);

        return redirect()->route('password.reset');
    }

    /**
     * Resends a new 6-digit OTP to the user's email in session and resets the expiration.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Please enter your email address first.']);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store hashed OTP in database, updating expiration to now()
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Send new OTP via email
        Mail::to($email)->send(new OtpMail($otp));

        return redirect()->route('password.verify')
            ->with('status', 'A new 6-digit OTP has been sent to your email address.');
    }

    /**
     * Displays the new password form. Accessible only if session is verified.
     */
    public function showResetForm(): View|RedirectResponse
    {
        if (!session('otp_verified') || !session('reset_email')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Please request and verify your OTP first.']);
        }

        return view('auth.reset-password');
    }

    /**
     * Validates password strength (>= Medium) and updates user password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        if (!session('otp_verified') || !session('reset_email')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Session expired. Please start the verification process again.']);
        }

        $request->validate([
            'password' => 'required|string|confirmed|min:8',
        ]);

        $password = $request->password;

        // Force Medium strength: min 8 characters, requires uppercase + lowercase + number
        if (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return back()->withErrors([
                'password' => 'The password must be at least Medium strength (at least 8 characters, containing uppercase, lowercase, and numeric characters).',
            ]);
        }

        $email = session('reset_email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'User account not found.']);
        }

        // Update password & clear must_change_password if active
        $user->update([
            'password' => Hash::make($password),
            'must_change_password' => false,
        ]);

        // Create audit log
        $roleDisplay = $user->role ? ucfirst($user->role->name) : 'User';
        \App\Models\AuditLog::create([
            'admin_name' => "{$user->name} ({$roleDisplay})",
            'action' => 'Password Reset (OTP)',
            'target' => "User: {$user->name} ({$user->email}) reset their password via email OTP",
            'timestamp' => now(),
        ]);

        // Clean up DB token and clear session variables
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget(['reset_email', 'otp_verified']);

        return redirect()->route('login')
            ->with('success', 'Your password has been reset successfully. Please log in with your new password.');
    }
}
