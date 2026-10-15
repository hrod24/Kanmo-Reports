<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Attendance Report')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Restore sidebar state before rendering --}}
    <script>
        if (localStorage.getItem('sidebar-hidden') === 'true') {
            document.documentElement.classList.add('sidebar-hidden');
        }
    </script>

    <style>
        @media (min-width: 768px) {
            .sidebar-hidden #sidebar {
                transform: translateX(-100%);
            }

            .sidebar-hidden #main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body class="antialiased bg-slate-50 text-slate-800">

    <div class="min-h-screen">

        {{-- Sidebar --}}
        <x-sidebar />

        {{-- Main Content --}}
        <main id="main-content" class="h-screen overflow-hidden transition-all duration-300 md:ml-64">
            {{-- Topbar --}}
            <header class="flex h-14 shrink-0 items-center border-b
               border-slate-200 bg-white px-5">
                {{-- Gunakan tombol sidebar-toggle yang sudah ada --}}
                <button id="sidebar-toggle" type="button" aria-label="Toggle sidebar" aria-controls="sidebar"
                    aria-expanded="true"
                    class="flex h-9 w-9 items-center justify-center
                   rounded-lg text-slate-500 hover:bg-slate-100">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <path d="M9 3v18" />
                    </svg>
                </button>

                <h2 class="ml-4 text-sm font-semibold text-slate-800">
                    @yield('title', 'Dashboard')
                </h2>
            </header>

            <div class="h-[calc(100dvh-56px)] overflow-y-auto p-3 lg:p-4
                xl:overflow-hidden">
                @yield('content')
            </div>
        </main>

    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggle = document.getElementById('sidebar-toggle');
        const close = document.getElementById('sidebar-close');

        const desktop = window.matchMedia('(min-width: 768px)');

        function isSidebarVisible() {
            if (desktop.matches) {
                return !document.documentElement.classList.contains('sidebar-hidden');
            }

            return !sidebar.classList.contains('-translate-x-full');
        }

        function syncSidebarState() {
            toggle.setAttribute(
                'aria-expanded',
                String(isSidebarVisible())
            );
        }

        function hideMobileSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            syncSidebarState();
        }

        toggle.addEventListener('click', () => {

            if (desktop.matches) {

                const hidden = document.documentElement.classList.toggle(
                    'sidebar-hidden'
                );

                localStorage.setItem('sidebar-hidden', String(hidden));

            } else {

                const isClosed = sidebar.classList.contains(
                    '-translate-x-full'
                );

                sidebar.classList.toggle('-translate-x-full', !isClosed);
                overlay.classList.toggle('hidden', !isClosed);

            }

            syncSidebarState();
        });

        close?.addEventListener('click', hideMobileSidebar);
        overlay?.addEventListener('click', hideMobileSidebar);

        desktop.addEventListener('change', () => {
            hideMobileSidebar();
            syncSidebarState();
        });

        syncSidebarState();
    </script>

</body>

</html>
