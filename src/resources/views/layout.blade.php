<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Dashboard</title>

    <!-- Tailwind CSS CDN for simplicity -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Optional dark mode styling -->
    <style>
        /* Dark mode styles */
        .dark {
            background-color: #1a202c; /* Dark background */
            color: #a0aec0; /* Text color for dark mode */
        }
        .dark input, .dark select, .dark textarea {
            background-color: #2d3748; /* Darker input background */
            color: #cbd5e0; /* Input text color */
        }
        .dark .bg-white {
            background-color: #2d3748 !important; /* Override white backgrounds */
        }
        .dark .bg-gray-100 {
            background-color: #4a5568 !important; /* Adjusted for dark mode */
        }
        .dark .text-gray-600 {
            color: #cbd5e0 !important;
        }
        .dark .border-gray-200 {
            border-color: #4a5568 !important;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased flex flex-col min-h-screen">
<!-- Header -->
<header class="bg-white shadow p-4">
    <div class="container mx-auto flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-white">LogHole Dashboard</h1>
        <a href="{{config('log-hole.dashboard_route')}}" class="text-gray-200 hover:text-blue-800">Home</a>
    </div>
</header>

<!-- Main Content -->
<main class="flex-grow p-4">
    <div class="container mx-auto">
        @yield('content')
    </div>
</main>

<!-- Footer -->
<footer class="bg-white border-t border-gray-200 text-center text-sm p-4">
    <p class="text-gray-500">&copy; {{ date('Y') }} <a href="https://digitaldev.pt" target="_blank">Digitaldev</a>. All rights reserved.</p>
</footer>
</body>
</html>
