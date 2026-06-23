@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 pb-12">
    @include('layouts.navigation')

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
                <p class="text-sm text-gray-500">Update your profile details and upload a photo</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm flex items-center shadow-sm">
                <i class="fas fa-check-circle mr-2 text-emerald-500 text-lg"></i>
                <div>
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm shadow-sm">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-2 text-rose-500 text-lg"></i>
                    <span class="font-semibold">Please correct the following errors:</span>
                </div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <!-- Header banner -->
            <div class="h-32 bg-gradient-to-r from-blue-600 to-indigo-700 relative"></div>
            
            <form id="settings-form" action="{{ route('profile.settings.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 -mt-16 relative">
                @csrf
                @method('PUT')

                <input type="hidden" name="remove_photo" id="remove_photo" value="0">

                <!-- Photo Upload Section -->
                <div class="flex flex-col sm:flex-row items-center sm:items-end space-y-4 sm:space-y-0 sm:space-x-6 mb-8 pb-6 border-b border-gray-100">
                    <div class="relative">
                        @if($user->profile_photo_path)
                            <img id="avatar-preview" class="h-28 w-28 rounded-full object-cover border-4 border-white shadow-md bg-white" src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}">
                        @else
                            <div id="avatar-initials" class="h-28 w-28 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-3xl border-4 border-white shadow-md shadow-emerald-200">
                                {{ $user->initials_avatar }}
                            </div>
                            <img id="avatar-preview" class="h-28 w-28 rounded-full object-cover border-4 border-white shadow-md bg-white hidden" src="#" alt="Preview">
                        @endif
                        <div id="avatar-initials" class="h-28 w-28 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-3xl border-4 border-white shadow-md shadow-emerald-200 hidden">
                            {{ $user->initials_avatar }}
                        </div>
                    </div>
                    
                    <div class="flex-grow text-center sm:text-left">
                        <h3 class="text-lg font-bold text-gray-950">Profile Photo</h3>
                        <p class="text-xs text-gray-500 mb-3">JPG, PNG or GIF. Max size 2MB.</p>
                        
                        <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                            <label class="inline-flex items-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg cursor-pointer transition shadow-xs">
                                <i class="fas fa-camera mr-2"></i> Upload Photo
                                <input type="file" name="profile_photo" id="profile_photo" class="hidden" accept="image/*" onchange="previewImage(this)">
                            </label>
                            
                            @if($user->profile_photo_path)
                                <button type="button" id="remove-photo-btn" onclick="confirmRemovePhoto()" class="inline-flex items-center px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-semibold rounded-lg transition shadow-xs">
                                    <i class="fas fa-trash-alt mr-2"></i> Remove Photo
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                    <!-- Enrollment Number (Students Only, Read-only) -->
                    @if($user->isStudent())
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">Enrollment Number</label>
                            <input type="text" value="{{ $user->enrollment_number }}" disabled class="w-full bg-gray-50 border border-gray-200 text-gray-400 rounded-lg py-2.5 px-4 cursor-not-allowed text-sm">
                        </div>
                    @endif



                    <!-- Roles Display (Read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">System Roles</label>
                        <input type="text" value="{{ $user->authority_display }}" disabled class="w-full bg-gray-50 border border-gray-200 text-gray-400 rounded-lg py-2.5 px-4 cursor-not-allowed text-sm">
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                    </div>

                    <!-- Department -->
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Department 
                            @if($user->isStudent() || $user->isFaculty() || $user->isHigherFaculty())
                                <span class="text-rose-500">*</span>
                            @endif
                        </label>
                        <input type="text" name="department" id="department" value="{{ old('department', $user->department) }}" @if($user->isStudent() || $user->isFaculty() || $user->isHigherFaculty()) required @endif class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                    </div>

                    <!-- Semester (Students Only) -->
                    @if($user->isStudent())
                        <div>
                            <label for="semester" class="block text-sm font-medium text-gray-700 mb-1.5">Semester <span class="text-rose-500">*</span></label>
                            <input type="number" name="semester" id="semester" min="1" max="10" value="{{ old('semester', $user->semester) }}" required class="w-full border border-gray-300 rounded-lg py-2.5 px-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                        </div>
                    @endif
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end space-x-3">
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-xs">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition shadow-md shadow-blue-200">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var preview = document.getElementById('avatar-preview');
                var initials = document.querySelectorAll('#avatar-initials');
                
                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                
                initials.forEach(function(el) {
                    el.classList.add('hidden');
                });
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    function confirmRemovePhoto() {
        if (confirm("Remove profile photo?\n\nThis will restore your default avatar.")) {
            document.getElementById('remove_photo').value = '1';
            
            // Hide preview image and show initials avatar
            var preview = document.getElementById('avatar-preview');
            var initials = document.querySelectorAll('#avatar-initials');
            
            if (preview) {
                preview.classList.add('hidden');
            }
            
            initials.forEach(function(el) {
                el.classList.remove('hidden');
            });
            
            // Hide remove photo button
            var removeBtn = document.getElementById('remove-photo-btn');
            if (removeBtn) {
                removeBtn.classList.add('hidden');
            }
            
            // Submit form immediately
            document.getElementById('settings-form').submit();
        }
    }
</script>
@endsection
