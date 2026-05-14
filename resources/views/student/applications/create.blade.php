@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
            <h1 class="text-4xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-file-alt text-blue-600 mr-3"></i>
                Submit Internship Application
            </h1>
            <p class="text-gray-600 mt-2">Fill in the details below to submit your internship NOC application</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-2xl mr-3"></i>
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Error Messages -->
        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-2xl mr-3"></i>
                    <p class="font-semibold">Please fix the following errors:</p>
                </div>
                <ul class="list-disc list-inside ml-8">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Application Form -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <form method="POST" action="{{ route('student.applications.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Company Information Section -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
                    <h3 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-building mr-3"></i>
                        Company Information
                    </h3>
                </div>

                <div class="p-8 space-y-6">
                    <!-- Company Name -->
                    <div>
                        <label for="company_name" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-building text-blue-600 mr-2"></i>Company Name *
                        </label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Enter company name">
                        @error('company_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Address -->
                    <div>
                        <label for="company_address" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>Company Address *
                        </label>
                        <textarea id="company_address" name="company_address" rows="3" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Enter complete company address">{{ old('company_address') }}</textarea>
                        @error('company_address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Email & Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="company_email" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-envelope text-blue-600 mr-2"></i>Company Email
                            </label>
                            <input type="email" id="company_email" name="company_email" value="{{ old('company_email') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                placeholder="company@example.com">
                            @error('company_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="company_phone" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-phone text-blue-600 mr-2"></i>Company Phone
                            </label>
                            <input type="text" id="company_phone" name="company_phone" value="{{ old('company_phone') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                placeholder="+91 1234567890">
                            @error('company_phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Company Website -->
                    <div>
                        <label for="company_website" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-globe text-blue-600 mr-2"></i>Company Website *
                        </label>
                        <input type="url" id="company_website" name="company_website" value="{{ old('company_website') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="https://www.company.com">
                        @error('company_website')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Branch Address -->
                    <div>
                        <label for="branch_address" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-map-marked-alt text-blue-600 mr-2"></i>Company Address (Branch where you want to go for internship) *
                        </label>
                        <textarea id="branch_address" name="branch_address" rows="2" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Enter branch address where internship will be conducted">{{ old('branch_address') }}</textarea>
                        @error('branch_address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Number of Employees -->
                    <div>
                        <label for="number_of_employees" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-users text-blue-600 mr-2"></i>Number of Employees *
                        </label>
                        <input type="text" id="number_of_employees" name="number_of_employees" value="{{ old('number_of_employees') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="e.g., 11 to 50 employees, 50-100 employees">
                        @error('number_of_employees')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Branch Locations -->
                    <div>
                        <label for="branch_locations" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-building text-blue-600 mr-2"></i>Number of Branches and Branch Locations (If Any)
                        </label>
                        <textarea id="branch_locations" name="branch_locations" rows="2"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="e.g., 3 branches - Mumbai, Delhi, Bangalore">{{ old('branch_locations') }}</textarea>
                        @error('branch_locations')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Head Office Address -->
                    <div>
                        <label for="head_office_address" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-landmark text-blue-600 mr-2"></i>Head Office Address (In Case of Multiple Branches)
                        </label>
                        <textarea id="head_office_address" name="head_office_address" rows="2"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Enter head office address if company has multiple branches">{{ old('head_office_address') }}</textarea>
                        @error('head_office_address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Contact Person Details Section -->
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-6">
                    <h3 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-user-tie mr-3"></i>
                        Contact Person Details
                    </h3>
                </div>

                <div class="p-8 space-y-6">
                    <!-- Contact Person Name -->
                    <div>
                        <label for="contact_person_name" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-user text-purple-600 mr-2"></i>Contact Person Name
                        </label>
                        <input type="text" id="contact_person_name" name="contact_person_name" value="{{ old('contact_person_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                            placeholder="Enter contact person name">
                        @error('contact_person_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Person Phone & Email -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="contact_person_phone" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-phone text-purple-600 mr-2"></i>Contact Person Phone Number
                            </label>
                            <input type="text" id="contact_person_phone" name="contact_person_phone" value="{{ old('contact_person_phone') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                                placeholder="+91 1234567890">
                            @error('contact_person_phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_person_email" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-envelope text-purple-600 mr-2"></i>Contact Person Email ID
                            </label>
                            <input type="email" id="contact_person_email" name="contact_person_email" value="{{ old('contact_person_email') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition"
                                placeholder="contact@company.com">
                            @error('contact_person_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- HR Details Section -->
                <div class="bg-gradient-to-r from-teal-600 to-cyan-600 p-6">
                    <h3 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-user-shield mr-3"></i>
                        HR Details
                    </h3>
                </div>

                <div class="p-8 space-y-6">
                    <!-- HR Name -->
                    <div>
                        <label for="hr_name" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-user-check text-teal-600 mr-2"></i>HR Name
                        </label>
                        <input type="text" id="hr_name" name="hr_name" value="{{ old('hr_name') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                            placeholder="Enter HR name">
                        @error('hr_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- HR Phone & Email -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="hr_phone" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-phone text-teal-600 mr-2"></i>HR Phone Number
                            </label>
                            <input type="text" id="hr_phone" name="hr_phone" value="{{ old('hr_phone') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                                placeholder="+91 1234567890">
                            @error('hr_phone')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="hr_email" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-envelope text-teal-600 mr-2"></i>HR Email Id
                            </label>
                            <input type="email" id="hr_email" name="hr_email" value="{{ old('hr_email') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition"
                                placeholder="hr@company.com">
                            @error('hr_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Company Work Details Section -->
                <div class="bg-gradient-to-r from-orange-600 to-red-600 p-6">
                    <h3 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-briefcase mr-3"></i>
                        Company Work Details
                    </h3>
                </div>

                <div class="p-8 space-y-6">
                    <!-- Technology -->
                    <div>
                        <label for="technology" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-code text-orange-600 mr-2"></i>Technology (Company Working On) *
                        </label>
                        <input type="text" id="technology" name="technology" value="{{ old('technology') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            placeholder="e.g., Web Development, Mobile Apps, AI/ML, Cloud Computing">
                        @error('technology')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Current Project -->
                    <div>
                        <label for="current_project" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-project-diagram text-orange-600 mr-2"></i>Current Project of Company (If Details Provided)
                        </label>
                        <textarea id="current_project" name="current_project" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            placeholder="Describe the current projects the company is working on">{{ old('current_project') }}</textarea>
                        @error('current_project')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Clients -->
                    <div>
                        <label for="clients" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-handshake text-orange-600 mr-2"></i>Clients of Company
                        </label>
                        <textarea id="clients" name="clients" rows="2"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            placeholder="List major clients or client types">{{ old('clients') }}</textarea>
                        @error('clients')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- How did you get this Company -->
                    <div>
                        <label for="how_did_you_get_company" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-search text-orange-600 mr-2"></i>How did you get this Company? *
                        </label>
                        <input type="text" id="how_did_you_get_company" name="how_did_you_get_company" value="{{ old('how_did_you_get_company') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            placeholder="e.g., Through LinkedIn, Email, Job Portal, Referral">
                        @error('how_did_you_get_company')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Reason to select this Company -->
                    <div>
                        <label for="reason_to_select_company" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-star text-orange-600 mr-2"></i>Reason to select this Company *
                        </label>
                        <textarea id="reason_to_select_company" name="reason_to_select_company" rows="4" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                            placeholder="Explain why you chose this company for your internship">{{ old('reason_to_select_company') }}</textarea>
                        @error('reason_to_select_company')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Internship Details Section -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
                    <h3 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-briefcase mr-3"></i>
                        Internship Details
                    </h3>
                </div>

                <div class="p-8 space-y-6">
                    <!-- Internship Position -->
                    <div>
                        <label for="internship_position" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-user-tie text-blue-600 mr-2"></i>Internship Position/Role *
                        </label>
                        <input type="text" id="internship_position" name="internship_position" value="{{ old('internship_position') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="e.g., Software Developer Intern, Marketing Intern">
                        @error('internship_position')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Internship Description -->
                    <div>
                        <label for="internship_description" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-align-left text-blue-600 mr-2"></i>Internship Description
                        </label>
                        <textarea id="internship_description" name="internship_description" rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="Describe the internship role, responsibilities, and learning outcomes...">{{ old('internship_description') }}</textarea>
                        @error('internship_description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start Date & End Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Start Date *
                            </label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            @error('start_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-bold text-gray-700 mb-2">
                                <i class="fas fa-calendar-check text-blue-600 mr-2"></i>End Date *
                            </label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            @error('end_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Documents Section -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
                    <h3 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-file-upload mr-3"></i>
                        Required Documents
                    </h3>
                </div>

                <div class="p-8 space-y-6">
                    <!-- Company Letter -->
                    <div>
                        <label for="company_letter" class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-file-pdf text-blue-600 mr-2"></i>Company Offer/Acceptance Letter (if you received offer letter then attach it)
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition">
                            <input type="file" id="company_letter" name="company_letter" accept=".pdf,.doc,.docx"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer">
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Accepted formats: PDF, DOC, DOCX (Max: 2MB)
                            </p>
                        </div>
                        @error('company_letter')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>


                </div>

                <!-- Submit Buttons -->
                <div class="bg-gray-50 px-8 py-6 flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-400 transition">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-300">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Application
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Text -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
            <h4 class="font-bold text-blue-900 mb-2 flex items-center">
                <i class="fas fa-lightbulb mr-2"></i>
                Important Information
            </h4>
            <ul class="text-sm text-blue-800 space-y-1 ml-6 list-disc">
                <li>All mandatory fields must be filled correctly</li>
                <li>Company letter is optional at the time of submission</li>
                <li>Your application will be reviewed by faculty first, then by higher faculty</li>
                <li>You will receive email notifications at each stage of the review process</li>
            </ul>
        </div>
    </div>
</div>
@endsection