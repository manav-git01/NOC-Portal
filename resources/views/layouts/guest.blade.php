<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/cspit_logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <!-- Font Awesome for Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
        
        <!-- Alpine.js for interactivity -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #ffffff;
            }
            
            .auth-card {
                background: #ffffff;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
                border: 2px solid #ffffffff;
            }
            
            .gradient-button {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
                transition: all 0.3s ease;
            }
            
            .gradient-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            }
            
            .input-icon {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: #2563eb;
                font-size: 16px;
                pointer-events: none;
                z-index: 10;
            }
            
            .input-with-icon {
                padding-left: 44px !important;
            }
            
            .input-with-icon::placeholder {
                color: #9ca3af;
                opacity: 0.8;
            }

            .info-box {
                background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
                border: 1px solid #bfdbfe;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-white">
            <!-- Auth Card -->
            <div class="auth-card w-full max-w-md rounded-2xl p-8 sm:p-10">
                <!-- Logo -->
                <div class="flex justify-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-800 rounded-full flex items-center justify-center shadow-md">
                        <i class="fas fa-certificate text-white text-2xl"></i>
                    </div>
                </div>
                
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
