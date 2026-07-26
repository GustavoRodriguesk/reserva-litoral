{{--
  NAVIGATION
  ─ Topbar: logo + 4 links principais + botão "Menu" + user dropdown
  ─ Drawer lateral: desliza da esquerda, fechado por padrão, contém todos os outros links agrupados
--}}
<div x-data="{ drawerOpen: false }" @keydown.escape.window="drawerOpen = false">

    {{-- ══════════════════════════════════════════════════════
         TOPBAR PRINCIPAL
    ══════════════════════════════════════════════════════ --}}
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-14 gap-2">

                {{-- Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 mr-4 shrink-0">
                    <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.707.707m12.728 12.728.708.708M3 12h1m16 0h1M4.22 19.78l.707-.707M18.364 5.636l.708-.708M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-slate-800 text-sm hidden sm:block">Reserva Litoral</span>
                </a>

                {{-- ── Links principais (desktop) ── --}}
                <div class="hidden md:flex items-center gap-1">
                    @php
                        $primary = [
                            ['route' => 'dashboard',          'match' => 'dashboard',      'label' => 'Dashboard'],
                            ['route' => 'reservations.index', 'match' => 'reservations.*', 'label' => 'Reservas'],
                            ['route' => 'guests.index',       'match' => 'guests.*',       'label' => 'Hóspedes'],
                            ['route' => 'planning.index',     'match' => 'planning.*',     'label' => 'Calendário'],
                            ['route' => 'housekeeping.index', 'match' => 'housekeeping.*', 'label' => 'Governança'],
                        ];
                    @endphp
                    @foreach($primary as $item)
                        @php $active = request()->routeIs($item['match']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                                  {{ $active
                                    ? 'bg-indigo-50 text-indigo-700'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>

                <div class="flex-1"></div>

                {{-- ── Nova Reserva (desktop) ── --}}
                <a href="{{ route('reservations.create') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Nova Reserva
                </a>

                {{-- ── Botão Menu (abre o drawer) ── --}}
                <button @click="drawerOpen = true"
                        id="btn-open-drawer"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg text-sm font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <span class="hidden sm:block">Menu</span>
                </button>

                {{-- ── User dropdown ── --}}
                <div class="relative" x-data="{ userOpen: false }">
                    <button @click="userOpen = !userOpen" @click.outside="userOpen = false"
                            class="flex items-center gap-2 rounded-lg pl-1 pr-2 py-1.5 hover:bg-slate-100 transition-colors">
                        <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-slate-700 max-w-[100px] truncate">{{ Auth::user()->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150" :class="{ 'rotate-180': userOpen }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>

                    <div x-show="userOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-full mt-1.5 w-48 bg-white rounded-xl border border-slate-200 shadow-lg py-1 z-50"
                         style="display:none">
                        <div class="px-3 py-2 border-b border-slate-100 mb-1">
                            <p class="text-xs font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                            Meu Perfil
                        </a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/></svg>
                                Sair
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    {{-- ══════════════════════════════════════════════════════
         OVERLAY
    ══════════════════════════════════════════════════════ --}}
    <div x-show="drawerOpen"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="drawerOpen = false"
         class="fixed inset-0 z-50 bg-black/30 backdrop-blur-sm"
         style="display:none">
    </div>

    {{-- ══════════════════════════════════════════════════════
         DRAWER LATERAL
    ══════════════════════════════════════════════════════ --}}
    <div x-show="drawerOpen"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 -translate-x-full"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-full"
         class="fixed top-0 left-0 h-full w-72 bg-white shadow-2xl z-50 flex flex-col"
         style="display:none">

        {{-- Header do drawer --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.707.707m12.728 12.728.708.708M3 12h1m16 0h1M4.22 19.78l.707-.707M18.364 5.636l.708-.708M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                    </svg>
                </div>
                <span class="font-bold text-slate-900 text-sm">Reserva Litoral</span>
            </div>
            <button @click="drawerOpen = false"
                    class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg p-1.5 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Links do drawer --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3">
            @php
                $sections = [
                    [
                        'label' => 'Operação',
                        'items' => [
                            ['route' => 'dashboard',          'match' => 'dashboard',           'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
                            ['route' => 'reservations.index', 'match' => 'reservations.*',       'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'Reservas'],
                            ['route' => 'guests.index',       'match' => 'guests.*',             'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Hóspedes'],
                            ['route' => 'planning.index',     'match' => 'planning.*',           'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Calendário'],
                            ['route' => 'housekeeping.index', 'match' => 'housekeeping.*',       'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'label' => 'Governança'],
                        ],
                    ],
                    [
                        'label' => 'Gestão',
                        'items' => [
                            ['route' => 'reports.index',   'match' => 'reports.*',    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'Relatórios'],
                            ['route' => 'rate-plans.index','match' => 'rate-plans.*', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'label' => 'Tarifários'],
                        ],
                    ],
                    [
                        'label' => 'Configurações',
                        'items' => [
                            ['route' => 'rooms.index',      'match' => 'rooms.*',       'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'label' => 'Quartos'],
                            ['route' => 'room-types.index', 'match' => 'room-types.*',  'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'label' => 'Categorias'],
                            ['route' => 'amenities.index',  'match' => 'amenities.*',   'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'label' => 'Comodidades'],
                            ['route' => 'settings.index',   'match' => 'settings.*',    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Configurações'],
                        ],
                    ],
                ];
            @endphp

            @foreach($sections as $section)
                <div class="mb-5">
                    <p class="px-2 mb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                        {{ $section['label'] }}
                    </p>
                    <ul class="space-y-0.5">
                        @foreach($section['items'] as $item)
                            @php $active = request()->routeIs($item['match']); @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   @click="drawerOpen = false"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                          {{ $active
                                            ? 'bg-indigo-50 text-indigo-700'
                                            : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    <svg class="w-4.5 h-4.5 shrink-0 {{ $active ? 'text-indigo-500' : 'text-slate-400' }}"
                                         style="width:1.125rem;height:1.125rem"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                                    </svg>
                                    {{ $item['label'] }}
                                    @if($active)
                                        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if(!$loop->last)
                    <hr class="border-slate-100 my-2">
                @endif
            @endforeach
        </nav>

        {{-- Footer do drawer: user info + atalho nova reserva --}}
        <div class="shrink-0 border-t border-slate-100 p-4 space-y-3">
            <a href="{{ route('reservations.create') }}"
               @click="drawerOpen = false"
               class="flex items-center justify-center gap-2 w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nova Reserva
            </a>
            <div class="flex items-center gap-3 px-1">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sair"
                            class="text-slate-400 hover:text-rose-500 transition-colors p-1.5 rounded-lg hover:bg-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
