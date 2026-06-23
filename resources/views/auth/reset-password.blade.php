<x-guest-layout>
    <!-- Page Title -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">
            Reset Password
        </h1>
        <p class="text-gray-500 text-sm font-medium">Set a new secure password for your account</p>
    </div>

    <!-- Email Hint -->
    <div class="text-center text-xs text-gray-600 mb-4 bg-gray-50 border border-gray-200 rounded-lg p-2.5 font-medium">
        Account: <span class="font-bold text-gray-800 font-mono">{{ session('reset_email') }}</span>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4" x-data="{
        password: '',
        password_confirmation: '',
        showPassword: false,
        showPasswordConfirm: false,
        get criteria() {
            return {
                length: this.password.length >= 8,
                uppercase: /[A-Z]/.test(this.password),
                lowercase: /[a-z]/.test(this.password),
                number: /[0-9]/.test(this.password),
                special: /[^A-Za-z0-9]/.test(this.password)
            };
        },
        get passedCount() {
            let count = 0;
            if (this.criteria.length) count++;
            if (this.criteria.uppercase) count++;
            if (this.criteria.lowercase) count++;
            if (this.criteria.number) count++;
            if (this.criteria.special) count++;
            return count;
        },
        get strength() {
            if (this.password.length === 0) return { label: 'None', color: 'bg-gray-200', text: 'text-gray-400', width: 'w-0', level: 0 };
            
            // To be at least Medium, it MUST be at least 8 characters and satisfy uppercase, lowercase, and number
            const hasMediumBase = this.criteria.length && this.criteria.uppercase && this.criteria.lowercase && this.criteria.number;
            
            if (!this.criteria.length || this.passedCount < 3) {
                return { label: 'Weak', color: 'bg-red-500', text: 'text-red-500', width: 'w-1/4', level: 1 };
            }
            if (!hasMediumBase) {
                return { label: 'Weak', color: 'bg-red-500', text: 'text-red-500', width: 'w-1/4', level: 1 };
            }
            if (this.passedCount === 4) {
                return { label: 'Strong', color: 'bg-indigo-500', text: 'text-indigo-500', width: 'w-3/4', level: 3 };
            }
            if (this.passedCount === 5) {
                return { label: 'Very Strong', color: 'bg-emerald-500', text: 'text-emerald-500', width: 'w-full', level: 4 };
            }
            return { label: 'Medium', color: 'bg-yellow-500', text: 'text-yellow-500', width: 'w-1/2', level: 2 };
        },
        get hasRequiredRules() {
            return this.criteria.length && this.criteria.uppercase && this.criteria.lowercase && this.criteria.number;
        },
        get isConfirmValid() {
            return this.password === this.password_confirmation && this.password.length > 0;
        },
        get canSubmit() {
            return this.hasRequiredRules && this.isConfirmValid;
        }
    }">
        @csrf

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                New Password
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-lock text-lg"></i>
                </div>
                <input 
                    id="password" 
                    :type="showPassword ? 'text' : 'password'" 
                    name="password" 
                    required 
                    x-model="password"
                    placeholder="Enter new password"
                    class="input-with-icon w-full py-2.5 px-4 pr-10 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
                <button 
                    type="button" 
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                >
                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Password Confirmation -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                Confirm New Password
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-check-double text-lg"></i>
                </div>
                <input 
                    id="password_confirmation" 
                    :type="showPasswordConfirm ? 'text' : 'password'" 
                    name="password_confirmation" 
                    required 
                    x-model="password_confirmation"
                    placeholder="Confirm new password"
                    class="input-with-icon w-full py-2.5 px-4 pr-10 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
                <button 
                    type="button" 
                    @click="showPasswordConfirm = !showPasswordConfirm"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                >
                    <i class="fas" :class="showPasswordConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Live Password Strength Bar -->
        <div class="space-y-1 pt-2" x-show="password.length > 0">
            <div class="flex justify-between items-center text-xs font-semibold">
                <span class="text-gray-500">Password Strength:</span>
                <span :class="strength.text" x-text="strength.label">None</span>
            </div>
            <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-300" :class="strength.color + ' ' + strength.width"></div>
            </div>
        </div>

        <!-- Requirement Checklist -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-xs space-y-2 text-gray-600 mt-2 font-semibold">
            <p class="font-bold text-gray-800 text-[11px] uppercase tracking-wider mb-1">Password Requirements:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div class="flex items-center space-x-2">
                    <i class="fas" :class="criteria.length ? 'fa-circle-check text-emerald-500' : 'fa-circle-dot text-gray-400'"></i>
                    <span :class="criteria.length ? 'text-gray-800' : 'text-gray-500'">At least 8 characters</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas" :class="criteria.uppercase ? 'fa-circle-check text-emerald-500' : 'fa-circle-dot text-gray-400'"></i>
                    <span :class="criteria.uppercase ? 'text-gray-800' : 'text-gray-500'">One uppercase letter</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas" :class="criteria.lowercase ? 'fa-circle-check text-emerald-500' : 'fa-circle-dot text-gray-400'"></i>
                    <span :class="criteria.lowercase ? 'text-gray-800' : 'text-gray-500'">One lowercase letter</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas" :class="criteria.number ? 'fa-circle-check text-emerald-500' : 'fa-circle-dot text-gray-400'"></i>
                    <span :class="criteria.number ? 'text-gray-800' : 'text-gray-500'">One numeric digit</span>
                </div>
                <div class="flex items-center space-x-2 sm:col-span-2">
                    <i class="fas" :class="criteria.special ? 'fa-circle-check text-emerald-500' : 'fa-circle-dot text-gray-400'"></i>
                    <span :class="criteria.special ? 'text-gray-800' : 'text-gray-500'">One special char (optional)</span>
                </div>
            </div>
            <p class="text-[10px] text-gray-500 italic mt-1.5 pt-1 border-t border-gray-200">
                Submit will be enabled once requirements (at least 8 characters, containing uppercase, lowercase, and numeric characters) are met and passwords match.
            </p>
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="gradient-button w-full py-2.5 text-white font-semibold rounded-lg transition mt-6"
            :disabled="!canSubmit"
            :class="!canSubmit ? 'opacity-50 cursor-not-allowed' : ''"
        >
            Reset Password
        </button>
    </form>
</x-guest-layout>
