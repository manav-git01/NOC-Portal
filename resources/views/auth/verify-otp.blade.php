<x-guest-layout>
    <!-- Page Title -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">
            Verify OTP
        </h1>
        <p class="text-gray-500 text-sm">Enter the 6-digit code sent to your email</p>
    </div>
    
    @if(session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-center text-xs text-green-700 font-semibold">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.verifyOtp') }}" class="space-y-4" x-data="{
        timeLeft: 600,
        digits: ['', '', '', '', '', ''],
        init() {
            // If the status message is present in the session, we should reset the timer
            @if(session('status'))
                sessionStorage.removeItem('otp_timer');
                sessionStorage.removeItem('otp_timer_start');
            @endif

            // Check if there is already a timer in sessionStorage to persist it across refresh
            if (sessionStorage.getItem('otp_timer')) {
                const elapsed = Math.floor((Date.now() - sessionStorage.getItem('otp_timer_start')) / 1000);
                this.timeLeft = Math.max(0, 600 - elapsed);
            } else {
                sessionStorage.setItem('otp_timer', 'active');
                sessionStorage.setItem('otp_timer_start', Date.now().toString());
            }

            const timer = setInterval(() => {
                if (this.timeLeft > 0) {
                    this.timeLeft--;
                } else {
                    clearInterval(timer);
                    sessionStorage.removeItem('otp_timer');
                    sessionStorage.removeItem('otp_timer_start');
                }
            }, 1000);

            // Focus the first input box automatically
            this.$nextTick(() => {
                const inputs = document.querySelectorAll('.otp-digit');
                if (inputs[0]) inputs[0].focus();
            });
        },
        get formattedTime() {
            const minutes = Math.floor(this.timeLeft / 60);
            const seconds = this.timeLeft % 60;
            return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        },
        get isComplete() {
            return this.digits.every(d => d !== '') && this.timeLeft > 0;
        },
        handleInput(e, index) {
            let val = e.target.value;
            // Only allow digits
            val = val.replace(/[^0-9]/g, '');
            if (val.length > 0) {
                this.digits[index] = val[val.length - 1];
                // Focus next input box
                if (index < 5) {
                    this.$nextTick(() => {
                        const inputs = document.querySelectorAll('.otp-digit');
                        if (inputs[index + 1]) inputs[index + 1].focus();
                    });
                }
            } else {
                this.digits[index] = '';
            }
        },
        handleKeyDown(e, index) {
            if (e.key === 'Backspace') {
                if (this.digits[index] === '') {
                    // Backspace moves to previous box if current is empty
                    if (index > 0) {
                        this.digits[index - 1] = '';
                        const inputs = document.querySelectorAll('.otp-digit');
                        if (inputs[index - 1]) inputs[index - 1].focus();
                    }
                } else {
                    this.digits[index] = '';
                }
                e.preventDefault();
            } else if (e.key === 'ArrowLeft') {
                if (index > 0) {
                    const inputs = document.querySelectorAll('.otp-digit');
                    if (inputs[index - 1]) inputs[index - 1].focus();
                }
            } else if (e.key === 'ArrowRight') {
                if (index < 5) {
                    const inputs = document.querySelectorAll('.otp-digit');
                    if (inputs[index + 1]) inputs[index + 1].focus();
                }
            }
        },
        handlePaste(e) {
            e.preventDefault();
            const clipboardData = e.clipboardData || window.clipboardData;
            const pastedData = clipboardData.getData('Text').replace(/[^0-9]/g, '').slice(0, 6);
            
            for (let i = 0; i < 6; i++) {
                if (i < pastedData.length) {
                    this.digits[i] = pastedData[i];
                } else {
                    this.digits[i] = '';
                }
            }

            // Focus the appropriate input after paste
            const nextFocusIndex = Math.min(pastedData.length, 5);
            const inputs = document.querySelectorAll('.otp-digit');
            if (inputs[nextFocusIndex]) {
                inputs[nextFocusIndex].focus();
            }
        }
    }">
        @csrf

        <!-- Email Hint -->
        <div class="text-center text-xs text-gray-600 mb-4 bg-gray-50 border border-gray-200 rounded-lg p-2.5 font-medium">
            Verifying: <span class="font-bold text-gray-800 font-mono">{{ session('reset_email') }}</span>
        </div>

        <!-- OTP Code Input -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2 text-center">
                One-Time Password (OTP)
            </label>
            <div class="relative flex justify-center w-full mx-auto" style="min-width: 280px; max-width: 360px;">
                <div class="flex justify-between gap-2 md:gap-3 w-full">
                    <template x-for="(digit, index) in digits" :key="index">
                        <input 
                            :id="'otp-' + index"
                            type="text" 
                            maxlength="1" 
                            inputmode="numeric"
                            pattern="[0-9]*"
                            class="otp-digit w-10 h-12 md:w-12 md:h-14 text-center border-2 border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-2xl font-bold font-mono text-gray-800"
                            :class="timeLeft <= 0 ? 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed' : ''"
                            x-model="digits[index]"
                            @input="handleInput($event, index)"
                            @keydown="handleKeyDown($event, index)"
                            @paste="handlePaste($event)"
                            :disabled="timeLeft <= 0"
                        />
                    </template>
                </div>
            </div>
            <!-- Hidden input to submit the concatenated 6-digit OTP -->
            <input type="hidden" name="otp" :value="digits.join('')" />
            <x-input-error :messages="$errors->get('otp')" class="mt-2 text-center" />
        </div>

        <!-- Timer -->
        <div class="text-center text-sm font-semibold text-gray-600 mt-4">
            <span x-show="timeLeft > 0">
                Code expires in: <span class="text-red-500 font-mono" x-text="formattedTime">10:00</span>
            </span>
            <span x-show="timeLeft <= 0" class="text-red-500 font-semibold" style="display: none;">
                OTP has expired.
            </span>
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="gradient-button w-full py-2.5 text-white font-semibold rounded-lg transition mt-6"
            :disabled="!isComplete"
            :class="!isComplete ? 'opacity-50 cursor-not-allowed' : ''"
        >
            Verify Code
        </button>

        <!-- Links / Resend Option -->
        <div class="pt-4 border-t border-blue-200 flex justify-between items-center text-sm">
            <a 
                href="#" 
                onclick="event.preventDefault(); document.getElementById('resend-otp-form').submit();"
                class="text-blue-600 hover:text-blue-700 font-medium"
            >
                Request New OTP
            </a>
            <a 
                href="{{ route('login') }}" 
                class="text-gray-500 hover:text-gray-700"
            >
                Cancel
            </a>
        </div>
    </form>

    <!-- Hidden Form for Resending OTP -->
    <form id="resend-otp-form" method="POST" action="{{ route('password.resendOtp') }}" class="hidden">
        @csrf
    </form>
</x-guest-layout>
