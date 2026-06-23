<x-guest-layout>
    <!-- Page Title -->
    <div class="text-center mb-6">
        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-key text-amber-600 text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-1">
            Change Your Password
        </h1>
        <p class="text-gray-500 text-sm">Your account requires a password change before continuing</p>
    </div>
    
    <!-- Info Box -->
    <div class="mb-6 p-3 bg-amber-50 border border-amber-200 rounded-lg">
        <p class="text-xs text-gray-700 text-center">
            <i class="fas fa-shield-alt text-amber-600 mr-1"></i>
            For security, please set a new personal password
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form id="password-form" method="POST" action="{{ route('password.change.update') }}" class="space-y-4">
        @csrf

        <!-- New Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                New Password
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
                    autofocus
                    autocomplete="new-password"
                    placeholder="Enter new password"
                    oninput="checkPasswordStrength(this.value)"
                    class="input-with-icon w-full py-2.5 px-4 pr-10 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
                <button 
                    type="button" 
                    onclick="togglePassword('password', 'toggleIcon1')"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm animate-none"
                >
                    <i id="toggleIcon1" class="fas fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />

            <!-- Live Password Strength Meter -->
            <div class="mt-3 space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold">
                    <span class="text-gray-500">Password Strength:</span>
                    <span id="strength-label" class="text-red-500 font-bold">Weak</span>
                </div>
                <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div id="strength-bar" class="h-full bg-red-500 w-1/4 transition-all duration-300"></div>
                </div>
            </div>

            <!-- Live Password Requirements Checklist -->
            <div class="mt-4 space-y-1.5 border-t border-gray-100 pt-3">
                <p class="text-xs font-bold text-gray-500 mb-2">Requirements Checklist:</p>
                <div id="req-len" class="flex items-center text-xs text-gray-400 transition-colors duration-200">
                    <i class="fas fa-circle text-[6px] mr-2 transition-colors duration-200"></i> Minimum 8 characters
                </div>
                <div id="req-upper" class="flex items-center text-xs text-gray-400 transition-colors duration-200">
                    <i class="fas fa-circle text-[6px] mr-2 transition-colors duration-200"></i> Uppercase letter
                </div>
                <div id="req-lower" class="flex items-center text-xs text-gray-400 transition-colors duration-200">
                    <i class="fas fa-circle text-[6px] mr-2 transition-colors duration-200"></i> Lowercase letter
                </div>
                <div id="req-num" class="flex items-center text-xs text-gray-400 transition-colors duration-200">
                    <i class="fas fa-circle text-[6px] mr-2 transition-colors duration-200"></i> Number
                </div>
                <div id="req-spec" class="flex items-center text-xs text-gray-400 transition-colors duration-200">
                    <i class="fas fa-circle text-[6px] mr-2 transition-colors duration-200"></i> Special character
                </div>
            </div>
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Confirm New Password
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-lock text-lg"></i>
                </div>
                <input 
                    id="password_confirmation" 
                    type="password" 
                    name="password_confirmation" 
                    required 
                    autocomplete="new-password"
                    placeholder="Confirm your new password"
                    class="input-with-icon w-full py-2.5 px-4 pr-10 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
                <button 
                    type="button" 
                    onclick="togglePassword('password_confirmation', 'toggleIcon2')"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm animate-none"
                >
                    <i id="toggleIcon2" class="fas fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            id="submit-btn"
            class="gradient-button w-full py-2.5 text-white font-semibold rounded-lg transition mt-6 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <i class="fas fa-check-circle mr-2"></i>
            Set New Password
        </button>

        <!-- Logout Link -->
        <div class="text-center pt-4 border-t border-blue-200">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 font-medium">
                    <i class="fas fa-sign-out-alt mr-1"></i> Sign out instead
                </button>
            </form>
        </div>
    </form>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        let currentStrength = 'Weak';

        function checkPasswordStrength(pwd) {
            const length8 = pwd.length >= 8;
            const length12 = pwd.length >= 12;
            const hasUpper = /[A-Z]/.test(pwd);
            const hasLower = /[a-z]/.test(pwd);
            const hasNumber = /[0-9]/.test(pwd);
            const hasSpecial = /[^A-Za-z0-9]/.test(pwd);

            updateChecklist('req-len', length8);
            updateChecklist('req-upper', hasUpper);
            updateChecklist('req-lower', hasLower);
            updateChecklist('req-num', hasNumber);
            updateChecklist('req-spec', hasSpecial);

            let level = 'Weak';
            
            if (length8) {
                const hasLetters = hasUpper || hasLower;
                if (hasLetters && hasNumber) {
                    level = 'Medium';
                    if (hasUpper && hasLower && hasNumber) {
                        level = 'Strong';
                        if (hasSpecial && length12) {
                            level = 'Very Strong';
                        }
                    }
                }
            }

            currentStrength = level;

            const label = document.getElementById('strength-label');
            const bar = document.getElementById('strength-bar');
            const submitBtn = document.getElementById('submit-btn');

            if (level === 'Weak') {
                if (label) {
                    label.textContent = 'Weak';
                    label.className = 'text-red-500 font-bold';
                }
                if (bar) {
                    bar.className = 'h-full bg-red-500 w-1/4 transition-all duration-300';
                }
                if (submitBtn) {
                    submitBtn.setAttribute('disabled', 'disabled');
                }
            } else if (level === 'Medium') {
                if (label) {
                    label.textContent = 'Medium';
                    label.className = 'text-amber-500 font-bold';
                }
                if (bar) {
                    bar.className = 'h-full bg-amber-500 w-2/4 transition-all duration-300';
                }
                if (submitBtn) {
                    submitBtn.removeAttribute('disabled');
                }
            } else if (level === 'Strong') {
                if (label) {
                    label.textContent = 'Strong';
                    label.className = 'text-blue-500 font-bold';
                }
                if (bar) {
                    bar.className = 'h-full bg-blue-500 w-3/4 transition-all duration-300';
                }
                if (submitBtn) {
                    submitBtn.removeAttribute('disabled');
                }
            } else if (level === 'Very Strong') {
                if (label) {
                    label.textContent = 'Very Strong';
                    label.className = 'text-emerald-500 font-bold';
                }
                if (bar) {
                    bar.className = 'h-full bg-emerald-500 w-full transition-all duration-300';
                }
                if (submitBtn) {
                    submitBtn.removeAttribute('disabled');
                }
            }
        }

        function updateChecklist(elementId, isMet) {
            const el = document.getElementById(elementId);
            if (el) {
                const icon = el.querySelector('i');
                if (isMet) {
                    el.className = 'flex items-center text-xs text-emerald-600 font-semibold transition-colors duration-200';
                    if (icon) {
                        icon.className = 'fas fa-check-circle mr-2 text-emerald-500 transition-colors duration-200';
                    }
                } else {
                    el.className = 'flex items-center text-xs text-gray-400 transition-colors duration-200';
                    if (icon) {
                        icon.className = 'fas fa-circle text-[6px] mr-2 text-gray-400 transition-colors duration-200';
                    }
                }
            }
        }

        // Intercept form submission
        document.getElementById('password-form').addEventListener('submit', function(e) {
            if (currentStrength === 'Weak') {
                e.preventDefault();
                alert('Cannot update password: Strength must be at least Medium.');
            }
        });

        // Run initial check
        document.addEventListener('DOMContentLoaded', function() {
            checkPasswordStrength('');
        });
    </script>
</x-guest-layout>
