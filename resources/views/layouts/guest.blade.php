<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Estilos Corporativos -->
        <style>
            :root {
                --corp-primary: #2c4370;
                --corp-primary-dark: #1e2f4d;
                --corp-primary-light: #3d5a8a;
                --corp-secondary: #ffffff;
            }
            body {
                background: linear-gradient(135deg, var(--corp-primary) 0%, var(--corp-primary-dark) 100%) !important;
                min-height: 100vh;
            }
            .bg-gray-100 { background: transparent !important; }
            .bg-white { background-color: rgba(255, 255, 255, 0.95) !important; }
            .text-gray-600 { color: var(--corp-primary) !important; }
            .text-gray-500 { color: var(--corp-primary-light) !important; }
            a.text-gray-600:hover, a.hover\:text-gray-900:hover { color: var(--corp-primary-dark) !important; }
            input:focus { 
                border-color: var(--corp-primary) !important; 
                --tw-ring-color: var(--corp-primary) !important;
            }
            .text-indigo-600, .focus\:ring-indigo-500:focus { 
                color: var(--corp-primary) !important;
                --tw-ring-color: var(--corp-primary) !important;
            }
            button[type="submit"], .bg-gray-800 {
                background-color: var(--corp-primary) !important;
                border-color: var(--corp-primary) !important;
            }
            button[type="submit"]:hover, .hover\:bg-gray-700:hover {
                background-color: var(--corp-primary-dark) !important;
            }
            .rounded-md.focus\:ring-indigo-500:focus {
                --tw-ring-color: var(--corp-primary) !important;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
