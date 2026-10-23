{{-- Mobile Overlay --}}
<div
    id="sidebar-overlay"
    class="fixed inset-0 z-40 hidden bg-slate-950/30 md:hidden"
></div>

<aside
    id="sidebar"
    class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 transition-transform duration-300 ease-in-out -translate-x-full bg-white border-r border-slate-200 md:translate-x-0"
>

    {{-- Logo --}}
    <div class="flex items-center h-16 px-5 border-b border-slate-100">

        <div
            class="flex items-center justify-center text-white bg-blue-600 rounded-lg h-9 w-9"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M8 2v4"/>
                <path d="M16 2v4"/>
                <rect width="18" height="18" x="3" y="4" rx="2"/>
                <path d="M3 10h18"/>
                <path d="m9 16 2 2 4-4"/>
            </svg>
        </div>

        <div class="ml-3">
            <h1 class="text-sm font-semibold text-slate-900">
                Attendance
            </h1>

            <p class="text-xs text-slate-400">
                Report System
            </p>
        </div>

        {{-- Mobile Close --}}
        <button
            id="sidebar-close"
            type="button"
            class="flex items-center justify-center ml-auto rounded-lg h-9 w-9 text-slate-400 hover:bg-slate-100 md:hidden"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M18 6 6 18"/>
                <path d="m6 6 12 12"/>
            </svg>
        </button>

    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-5">

        <div class="space-y-1">

            {{-- Dashboard --}}
            <a
                href="{{ route('dashboard') }}"
                class="
                    group flex items-center gap-3 rounded-lg px-3 py-2.5
                    text-sm font-medium transition

                    {{ request()->routeIs('dashboard')
                        ? 'bg-blue-50 text-blue-600'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'
                    }}
                "
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-[18px] w-[18px]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <rect width="7" height="9" x="3" y="3" rx="1"/>
                    <rect width="7" height="5" x="14" y="3" rx="1"/>
                    <rect width="7" height="9" x="14" y="12" rx="1"/>
                    <rect width="7" height="5" x="3" y="16" rx="1"/>
                </svg>

                <span>
                    Dashboard
                </span>
            </a>

            {{-- Reports --}}
            <a
                href="{{ route('reports') }}"
                class="
                    group flex items-center gap-3 rounded-lg px-3 py-2.5
                    text-sm font-medium transition

                    {{ request()->routeIs('reports')
                        ? 'bg-blue-50 text-blue-600'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'
                    }}
                "
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-[18px] w-[18px]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M3 3v18h18"/>
                    <path d="m7 16 4-5 4 3 5-7"/>
                </svg>

                <span>
                    Reports
                </span>
            </a>

            {{-- Upload Data --}}
            <a href="{{ route('reminders.index') }}"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('reminders.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
                </svg>
                <span>Reminder</span>
            </a>

            <a
                href="{{ route('upload.index') }}"
                class="
                    group flex items-center gap-3 rounded-lg px-3 py-2.5
                    text-sm font-medium transition

                    {{ request()->routeIs('upload.index')
                        ? 'bg-blue-50 text-blue-600'
                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'
                    }}
                "
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-[18px] w-[18px]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path d="M12 3v12"/>
                    <path d="m7 8 5-5 5 5"/>
                    <path d="M5 15v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/>
                </svg>

                <span>
                    Upload Data
                </span>
            </a>

        </div>

    </nav>

    {{-- Sidebar Footer --}}
    <div class="p-4 border-t border-slate-100">

        <div class="flex items-center gap-3">

            <div
                class="flex items-center justify-center text-xs font-semibold rounded-full h-9 w-9 bg-slate-100 text-slate-600"
            >
                AR
            </div>

            <div class="min-w-0">
                <p class="text-sm font-medium truncate text-slate-700">
                    Attendance Report
                </p>

                <p class="text-xs truncate text-slate-400">
                    Management System
                </p>
            </div>

        </div>

    </div>

</aside>
