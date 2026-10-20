<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Attendance Report')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="antialiased bg-slate-50 text-slate-800">

    <div class="min-h-screen">

        {{-- Main Content --}}
        <main class="flex flex-col overflow-hidden transition-all duration-300 h-dvh">

            {{-- Content Area --}}
            <div
                class="min-h-0 flex-1 bg-[#F3F5FA] p-3 lg:p-4
        {{ request()->routeIs('dashboard') ? 'overflow-y-auto lg:overflow-hidden' : 'overflow-y-auto' }}">
                @yield('content')
            </div>
        </main>

    </div>

</body>

</html>
