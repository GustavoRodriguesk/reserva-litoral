{{-- Topbar — faixa superior enxuta com hamburguer mobile + breadcrumb + ações --}}
<header class="sticky top-0 z-10 flex items-center h-14 px-4 sm:px-6 bg-white border-b border-slate-100 shadow-sm gap-3">

    {{-- Hamburguer (mobile) --}}
    <button @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden text-slate-400 hover:text-slate-700 transition p-1 rounded-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
        </svg>
    </button>

    {{-- Breadcrumb / Título da página atual --}}
    @php
        $pageTitle = match(true) {
            request()->routeIs('dashboard')          => 'Dashboard',
            request()->routeIs('reservations.*')     => 'Reservas',
            request()->routeIs('guests.*')           => 'Hóspedes',
            request()->routeIs('planning.*')         => 'Calendário',
            request()->routeIs('housekeeping.*')     => 'Governança',
            request()->routeIs('reports.*')          => 'Relatórios',
            request()->routeIs('rooms.*')            => 'Quartos',
            request()->routeIs('room-types.*')       => 'Tipos de Quarto',
            request()->routeIs('rate-plans.*')       => 'Tarifários',
            request()->routeIs('amenities.*')        => 'Comodidades',
            request()->routeIs('settings.*')         => 'Configurações',
            request()->routeIs('invoices.*')         => 'Faturas',
            request()->routeIs('profile.*')          => 'Meu Perfil',
            default                                  => config('app.name'),
        };
    @endphp
    <span class="text-sm font-semibold text-slate-600 truncate">{{ $pageTitle }}</span>

    <div class="flex-1"></div>

    {{-- Nova Reserva --}}
    <a href="{{ route('reservations.create') }}"
       class="hidden sm:inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Nova Reserva
    </a>

    {{-- User dropdown --}}
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" @click.outside="open = false"
                class="flex items-center gap-2 rounded-xl px-2 py-1.5 hover:bg-slate-100 transition">
            <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <span class="hidden sm:block text-sm font-semibold text-slate-700 max-w-[120px] truncate">{{ Auth::user()->name }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl border border-slate-100 shadow-lg py-1 z-50"
             style="display:none">
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                Meu Perfil
            </a>
            <div class="border-t border-slate-100 my-1"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/></svg>
                    Sair
                </button>
            </form>
        </div>
    </div>
</header>
