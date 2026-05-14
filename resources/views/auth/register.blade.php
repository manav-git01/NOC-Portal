<x-guest-layout>
    <!-- Page Title -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">
            Create Account
        </h1>
        <p class="text-gray-500 text-sm">Join CHARUSAT Internship Portal</p>
    </div>
    
    <!-- Info Box -->
    <div class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-xs text-gray-700 text-center">
            <i class="fas fa-info-circle text-blue-600 mr-1"></i>
            Use your institutional email (@edu.in or @ac.in)
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-3.5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Full Name
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-user text-lg"></i>
                </div>
                <input 
                    id="name" 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    autofocus 
                    autocomplete="name"
                    placeholder="Enter your name"
                    class="input-with-icon w-full py-2.5 px-4 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

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
                    autocomplete="username"
                    placeholder="your email"
                    class="input-with-icon w-full py-2.5 px-4 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
            </div>
            <p id="email-hint" class="text-xs text-blue-600 mt-1">
                <i class="fas fa-info-circle"></i> Select role first
            </p>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Phone -->
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                Phone Number
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-phone text-lg"></i>
                </div>
                <input 
                    id="phone" 
                    type="text" 
                    name="phone" 
                    value="{{ old('phone') }}" 
                    required
                    placeholder="Enter phone"
                    class="input-with-icon w-full py-2.5 px-4 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
            </div>
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        <!-- Role -->
        <div>
            <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">
                Role
            </label>
            <div class="relative">
                <div class="input-icon">
                    <i class="fas fa-user-tag text-lg"></i>
                </div>
                <select 
                    id="role_id" 
                    name="role_id" 
                    required
                    class="input-with-icon w-full py-2.5 px-4 pr-10 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition appearance-none"
                >
                    <option value="">Select role</option>
                    <option value="1" {{ old('role_id') == '1' ? 'selected' : '' }}>Student</option>
                    <option value="2" {{ old('role_id') == '2' ? 'selected' : '' }}>Faculty</option>
                    <option value="3" {{ old('role_id') == '3' ? 'selected' : '' }}>Higher Faculty</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
            </div>
            <x-input-error :messages="$errors->get('role_id')" class="mt-1" />
        </div>

        <!-- Student-specific fields -->
        <div id="student-fields" style="display: none;" class="space-y-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm font-medium text-blue-800">
                <i class="fas fa-graduation-cap mr-1"></i> Student Info
            </p>
            
            <div>
                <label for="enrollment_number" class="block text-xs font-medium text-gray-700 mb-1">
                    Enrollment Number
                </label>
                <input 
                    id="enrollment_number" 
                    type="text" 
                    name="enrollment_number" 
                    value="{{ old('enrollment_number') }}"
                    placeholder="e.g., 21IT001"
                    class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
                />
                <x-input-error :messages="$errors->get('enrollment_number')" class="mt-1" />
            </div>

            <div>
                <label for="department" class="block text-xs font-medium text-gray-700 mb-1">
                    Department
                </label>
                <input 
                    id="department" 
                    type="text" 
                    name="department" 
                    value="{{ old('department') }}"
                    placeholder="e.g., IT"
                    class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
                />
                <x-input-error :messages="$errors->get('department')" class="mt-1" />
            </div>

            <div>
                <label for="semester" class="block text-xs font-medium text-gray-700 mb-1">
                    Semester
                </label>
                <select 
                    id="semester" 
                    name="semester"
                    class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
                >
                    <option value="">Select semester</option>
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                    @endfor
                </select>
                <x-input-error :messages="$errors->get('semester')" class="mt-1" />
            </div>
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
                    autocomplete="new-password"
                    placeholder="Min 8 characters"
                    class="input-with-icon w-full py-2.5 px-4 pr-10 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
                <button 
                    type="button" 
                    onclick="togglePassword('password', 'toggleIcon1')"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm"
                >
                    <i id="toggleIcon1" class="fas fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Confirm Password
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
                    placeholder="Confirm password"
                    class="input-with-icon w-full py-2.5 px-4 pr-10 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                />
                <button 
                    type="button" 
                    onclick="togglePassword('password_confirmation', 'toggleIcon2')"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm"
                >
                    <i id="toggleIcon2" class="fas fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Create Account Button -->
        <button 
            type="submit" 
            class="gradient-button w-full py-2.5 text-white font-semibold rounded-lg transition mt-6"
        >
            Create Account
        </button>

        <!-- Login Link -->
        <p class="text-sm text-gray-600 text-center pt-4 border-t border-blue-200">
            Already have an account? 
            <a 
                href="{{ route('login') }}" 
                class="text-blue-600 hover:text-blue-700 font-semibold"
            >
                Sign in
            </a>
        </p>
    </form>

    <script>
        const roleSelect = document.getElementById('role_id');
        const emailInput = document.getElementById('email');
        const emailHint = document.getElementById('email-hint');
        const registerForm = document.querySelector('form');

        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
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

        function validateEmailDomain() {
            const roleId = roleSelect.value;
            const email = emailInput.value.toLowerCase().trim();
            
            if (!roleId) {
                emailHint.innerHTML = '<i class="fas fa-info-circle"></i> Select role first';
                emailHint.className = 'text-xs text-blue-600 mt-1';
                emailInput.classList.remove('border-red-500', 'border-green-500');
                return false;
            }

            if (!email) {
                emailHint.innerHTML = '<i class="fas fa-info-circle"></i> Select role first';
                emailHint.className = 'text-xs text-blue-600 mt-1';
                emailInput.classList.remove('border-red-500', 'border-green-500');
                return false;
            }

            if (roleId === '1') {
                const studentEmailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.edu\.in$/;
                if (!studentEmailPattern.test(email)) {
                    emailHint.innerHTML = '<i class="fas fa-times-circle"></i> Use @edu.in email';
                    emailHint.className = 'text-xs text-red-600 mt-1';
                    emailInput.classList.add('border-red-500');
                    emailInput.classList.remove('border-green-500');
                    return false;
                } else {
                    emailHint.innerHTML = '<i class="fas fa-check-circle"></i> Valid email';
                    emailHint.className = 'text-xs text-green-600 mt-1';
                    emailInput.classList.add('border-green-500');
                    emailInput.classList.remove('border-red-500');
                    return true;
                }
            }
            
            if (roleId === '2' || roleId === '3') {
                const facultyEmailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.ac\.in$/;
                if (!facultyEmailPattern.test(email)) {
                    emailHint.innerHTML = '<i class="fas fa-times-circle"></i> Use @ac.in email';
                    emailHint.className = 'text-xs text-red-600 mt-1';
                    emailInput.classList.add('border-red-500');
                    emailInput.classList.remove('border-green-500');
                    return false;
                } else {
                    emailHint.innerHTML = '<i class="fas fa-check-circle"></i> Valid email';
                    emailHint.className = 'text-xs text-green-600 mt-1';
                    emailInput.classList.add('border-green-500');
                    emailInput.classList.remove('border-red-500');
                    return true;
                }
            }

            return false;
        }

        roleSelect.addEventListener('change', function() {
            const studentFields = document.getElementById('student-fields');
            const enrollmentInput = document.getElementById('enrollment_number');
            const departmentInput = document.getElementById('department');
            const semesterInput = document.getElementById('semester');
            
            if (this.value === '1') {
                studentFields.style.display = 'block';
                enrollmentInput.required = true;
                departmentInput.required = true;
                semesterInput.required = true;
            } else {
                studentFields.style.display = 'none';
                enrollmentInput.required = false;
                departmentInput.required = false;
                semesterInput.required = false;
            }

            validateEmailDomain();
        });

        emailInput.addEventListener('input', function() {
            validateEmailDomain();
        });

        emailInput.addEventListener('blur', function() {
            validateEmailDomain();
        });

        registerForm.addEventListener('submit', function(e) {
            const isValid = validateEmailDomain();
            
            if (!isValid) {
                e.preventDefault();
                emailInput.focus();
                return false;
            }
        });
    </script>
</x-guest-layout>
