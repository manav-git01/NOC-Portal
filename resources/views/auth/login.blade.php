<x-guest-layout>
    <!-- Page Title -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">
            Welcome Back
        </h1>
        <p class="text-gray-500 text-sm">Sign in to your account</p>
    </div>
    
    <!-- Info Box -->
    <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-xs text-gray-700 text-center">
            <i class="fas fa-shield-alt text-blue-600 mr-1"></i>
            Use your institutional email to sign in
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
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
                    autocomplete="username"
                    placeholder="your email"
                    class="input-with-icon w-full py-2.5 px-4 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Password
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-lock text-lg"></i>
                </div>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    placeholder="Enter password"
                    class="input-with-icon w-full py-2.5 px-4 pr-10 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
                <button 
                    type="button" 
                    onclick="togglePassword()"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm"
                >
                    <i id="toggleIcon" class="fas fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input 
                id="remember_me" 
                type="checkbox" 
                name="remember"
                class="w-4 h-4 text-blue-600 border-blue-300 rounded focus:ring-blue-500"
            >
            <label for="remember_me" class="ml-2 text-sm text-gray-600">
                Remember me
            </label>
        </div>

        <!-- Sign In Button -->
        <button 
            type="submit" 
            class="gradient-button w-full py-2.5 text-white font-semibold rounded-lg transition mt-6"
        >
            Sign In
        </button>

        <!-- Links -->
        <div class="space-y-2 pt-4 border-t border-blue-200">
            @if (Route::has('password.request'))
                <div class="text-center">
                    <a 
                        href="{{ route('password.request') }}" 
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        Forgot password?
                    </a>
                </div>
            @endif

            <p class="text-sm text-gray-600 text-center">
                Don't have an account? 
                <a 
                    href="{{ route('register') }}" 
                    class="text-blue-600 hover:text-blue-700 font-semibold"
                >
                    Register
                </a>
            </p>
        </div>
    </form>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</x-guest-layout>
