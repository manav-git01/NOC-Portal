@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-12">
    @include('layouts.navigation')

    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Change Password</h1>
                <p class="text-sm text-gray-500">Ensure your account is using a secure password</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        @if(session('status') === 'password-updated')
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center shadow-sm">
                <i class="fas fa-check-circle mr-2 text-emerald-500 text-lg"></i>
                <div>
                    <p class="font-semibold">Password updated successfully.</p>
                </div>
            </div>
        @endif

        @if($errors->updatePassword->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm shadow-sm">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-2 text-rose-500 text-lg"></i>
                    <span class="font-semibold">Password update failed:</span>
                </div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->updatePassword->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 sm:p-8">
                <form id="password-form" action="{{ route('profile.password.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1.5">Current Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input 
                                id="current_password" 
                                type="password" 
                                name="current_password" 
                                required 
                                class="pl-10 pr-10 w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
                                placeholder="Enter current password"
                            />
                            <button 
                                type="button" 
                                onclick="togglePassword('current_password', 'toggleIconCurrent')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm animate-none"
                            >
                                <i id="toggleIconCurrent" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fas fa-key"></i>
                            </span>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                required 
                                class="pl-10 pr-10 w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
                                placeholder="Enter secure new password"
                                oninput="checkPasswordStrength(this.value)"
                            />
                            <button 
                                type="button" 
                                onclick="togglePassword('password', 'toggleIconNew')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm animate-none"
                            >
                                <i id="toggleIconNew" class="fas fa-eye"></i>
                            </button>
                        </div>

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

                    <!-- Confirm New Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <i class="fas fa-key"></i>
                            </span>
                            <input 
                                id="password_confirmation" 
                                type="password" 
                                name="password_confirmation" 
                                required 
                                class="pl-10 pr-10 w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm"
                                placeholder="Re-type new password"
                            />
                            <button 
                                type="button" 
                                onclick="togglePassword('password_confirmation', 'toggleIconConfirm')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 text-sm animate-none"
                            >
                                <i id="toggleIconConfirm" class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-xs">
                            Cancel
                        </a>
                        <button type="submit" id="submit-btn" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-md shadow-blue-200 cursor-pointer disabled:cursor-not-allowed">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
        // Requirements
        const length8 = pwd.length >= 8;
        const length12 = pwd.length >= 12;
        const hasUpper = /[A-Z]/.test(pwd);
        const hasLower = /[a-z]/.test(pwd);
        const hasNumber = /[0-9]/.test(pwd);
        const hasSpecial = /[^A-Za-z0-9]/.test(pwd);

        // Checklist indicators
        updateChecklist('req-len', length8);
        updateChecklist('req-upper', hasUpper);
        updateChecklist('req-lower', hasLower);
        updateChecklist('req-num', hasNumber);
        updateChecklist('req-spec', hasSpecial);

        // Determine level
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

        // UI Updates for Strength Bar & Label
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
                submitBtn.classList.add('opacity-50');
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
                submitBtn.classList.remove('opacity-50');
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
                submitBtn.classList.remove('opacity-50');
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
                submitBtn.classList.remove('opacity-50');
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
@endsection
