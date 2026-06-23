<x-guest-layout>
    <!-- Page Title -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">
            Forgot Password
        </h1>
        <p class="text-gray-500 text-sm">Enter your registered email to receive an OTP</p>
    </div>
    
    <!-- Info Box -->
    <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-xs text-gray-700 text-center">
            <i class="fas fa-key text-blue-600 mr-1"></i>
            A 6-digit verification code will be sent to your email.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-envelope text-lg"></i>
                </div>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    placeholder="Enter email address"
                    class="input-with-icon w-full py-2.5 px-4 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="gradient-button w-full py-2.5 text-white font-semibold rounded-lg transition mt-6"
        >
            Send Verification Code
        </button>

        <!-- Links -->
        <div class="space-y-2 pt-4 border-t border-blue-200 text-center">
            <a 
                href="{{ route('login') }}" 
                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
            >
                Back to Login
            </a>
        </div>
    </form>
</x-guest-layout>
