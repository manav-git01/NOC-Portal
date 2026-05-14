<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InternshipNOC Portal - Home</title>
    <link rel="icon" type="image/png" href="{{ asset('images/cspit_logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .stat-card {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }
        
        .btn-hover {
            transition: all 0.3s ease;
        }
        
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
        }

        .benefit-card {
            transition: all 0.3s ease;
        }
        
        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
        <!-- Top Navigation Bar -->
        <nav class="bg-white shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo and Brand -->
                    <div class="flex items-center space-x-3">
                        <img src="{{ asset('images/cspit_logo.png') }}" alt="CSPIT Logo" class="w-10 h-10 object-contain">
                        <span class="text-gray-800 text-xl font-bold">InternshipNOC Portal</span>
                    </div>
                    
                    <!-- Auth Buttons -->
                    <div class="flex items-center space-x-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-semibold btn-hover text-sm">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-5 py-2 border-2 border-blue-500 text-blue-600 rounded-lg font-semibold btn-hover text-sm hover:bg-blue-50">
                                LOGIN
                            </a>
                            <a href="{{ route('register') }}" class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg font-semibold btn-hover text-sm">
                                Register
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <h1 class="text-5xl md:text-6xl font-bold text-gray-800 mb-4">
                    Welcome to <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">InternshipNOC Portal</span>
                </h1>
                <p class="text-gray-600 text-lg max-w-3xl mx-auto leading-relaxed">
                    Streamline your internship applications and NOC requests with our modern, user-friendly portal designed for CSPIT IT department students.
                </p>
            </div>
            
            <!-- CTA Buttons -->
            <div class="flex flex-wrap justify-center gap-4 mb-16">
                @guest
                    <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-bold text-lg btn-hover shadow-lg hover:shadow-xl">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-4 border-2 border-blue-500 text-blue-600 rounded-xl font-bold text-lg btn-hover hover:bg-blue-50">
                        <i class="fas fa-user-plus mr-2"></i>
                        Register
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl font-bold text-lg btn-hover shadow-lg hover:shadow-xl">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Go to Dashboard
                    </a>
                @endguest
            </div>
            
            <!-- Statistics Cards - Representing all three dashboards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <!-- Student Dashboard Stats -->
                <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium mb-1">Easy Application</p>
                            <h3 class="text-3xl font-bold">For Students</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-file-alt text-3xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Submit applications easily</span>
                    </div>
                </div>
                
                <div class="stat-card bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-sm font-medium mb-1">Faculty Review</p>
                            <h3 class="text-3xl font-bold">First Level</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-user-tie text-3xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Faculty approval process</span>
                    </div>
                </div>
                
                <div class="stat-card bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium mb-1">Higher Faculty</p>
                            <h3 class="text-3xl font-bold">Second Level</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-crown text-3xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>Final approval & NOC generation</span>
                    </div>
                </div>
                
                <div class="stat-card bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-100 text-sm font-medium mb-1">Instant NOC</p>
                            <h3 class="text-3xl font-bold">Automatic</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-certificate text-3xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <i class="fas fa-download mr-2"></i>
                        <span>Download your NOC</span>
                    </div>
                </div>
            </div>
            
            <!-- Features Section -->
            <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12 mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 text-center mb-12">Portal Features</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="feature-card text-center p-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <i class="fas fa-file-upload text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Easy Submission</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Students can quickly submit internship details and upload offer letters in a few simple steps.
                        </p>
                    </div>
                    
                    <!-- Feature 2 -->
                    <div class="feature-card text-center p-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <i class="fas fa-user-check text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Multi-Level Approval</h3>
                        <p class="text-gray-600 leading-relaxed">
                            A transparent review process involving department training and placement officers.
                        </p>
                    </div>
                    
                    <!-- Feature 3 -->
                    <div class="feature-card text-center p-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <i class="fas fa-file-pdf text-white text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Digital NOC</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Instantly download your professionally generated PDF NOC once final approval is granted.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Benefits Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <div class="benefit-card bg-white rounded-2xl shadow-lg p-8 border-l-4 border-blue-500">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Save Time</h3>
                            <p class="text-gray-600">Eliminate manual paperwork and streamline the entire application process</p>
                        </div>
                    </div>
                </div>
                
                <div class="benefit-card bg-white rounded-2xl shadow-lg p-8 border-l-4 border-orange-500">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-eye text-orange-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Track Progress</h3>
                            <p class="text-gray-600">Monitor your application status in real-time from submission to approval</p>
                        </div>
                    </div>
                </div>
                
                <div class="benefit-card bg-white rounded-2xl shadow-lg p-8 border-l-4 border-green-500">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-lock text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Secure & Reliable</h3>
                            <p class="text-gray-600">Your data is protected with modern security standards and encrypted storage</p>
                        </div>
                    </div>
                </div>
                
                <div class="benefit-card bg-white rounded-2xl shadow-lg p-8 border-l-4 border-red-500">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file-pdf text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Digital NOC</h3>
                            <p class="text-gray-600">Download your NOC certificate instantly in professional PDF format</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-white shadow-lg border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-gray-600">
                <p>&copy; {{ date('Y') }} InternshipNOC Portal. All rights reserved. | CSPIT IT Department</p>
            </div>
        </div>
    </div>
</body>
</html>
