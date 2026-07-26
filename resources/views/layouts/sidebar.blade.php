{{--
  SIDEBAR
  ─ Expandida (w-64) no desktop quando sidebarCollapsed = false
  ─ Colapsada (w-16, só ícones) no desktop quando sidebarCollapsed = true
  ─ Off-canvas no mobile, exibida ao acionar sidebarOpen = true
--}}

@php
    $nav = [
        'operacao' => [
            'label' => 'Operação',
            'items' => [
                ['route' => 'dashboard',          'match' => 'dashboard',           'label' => 'Dashboard',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                ['route' => 'reservations.index', 'match' => 'reservations.*',      'label' => 'Reservas',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
                ['route' => 'guests.index',       'match' => 'guests.*',            'label' => 'Hóspedes',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>'],
                ['route' => 'planning.index',     'match' => 'planning.*',          'label' => 'Calendário',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
                ['route' => 'housekeeping.index', 'match' => 'housekeeping.*',      'label' => 'Governança',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>'],
            ],
        ],
        'gestao' => [
            'label' => 'Gestão',
            'items' => [
                ['route' => 'reports.index',    'match' => 'reports.*',    'label' => 'Relatórios',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
                ['route' => 'invoices.show',    'match' => 'invoices.*',   'label' => 'Faturas',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'disabled' => true],
            ],
        ],
        'configuracoes' => [
            'label' => 'Configurações',
            'items' => [
                ['route' => 'rooms.index',       'match' => 'rooms.*',       'label' => 'Quartos',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                ['route' => 'room-types.index',  'match' => 'room-types.*',  'label' => 'Categorias',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>'],
                ['route' => 'rate-plans.index',  'match' => 'rate-plans.*',  'label' => 'Tarifários',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
                ['route' => 'amenities.index',   'match' => 'amenities.*',   'label' => 'Comodidades',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>'],
                ['route' => 'settings.index',    'match' => 'settings.*',    'label' => 'Configurações','icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
            ],
        ],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-30 flex flex-col bg-slate-900 transition-all duration-300 ease-in-out
           lg:translate-x-0
           w-64"
    :class="{
        '-translate-x-full lg:translate-x-0': !sidebarOpen,
        'translate-x-0': sidebarOpen,
        'lg:w-64': !sidebarCollapsed,
        'lg:w-16': sidebarCollapsed,
    }"
>
    {{-- Logo + Toggle --}}
    <div class="flex items-center justify-between h-16 px-4 border-b border-slate-800 shrink-0">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-2.5 min-w-0 overflow-hidden"
           :class="{ 'justify-center w-full': sidebarCollapsed }">
            {{-- Ícone âncora --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1M4.22 4.22l.707.707m12.728 12.728.708.708M3 12h1m16 0h1M4.22 19.78l.707-.707M18.364 5.636l.708-.708M12 8a4 4 0 100 8 4 4 0 000-8z"/>
            </svg>
            <span class="text-white font-bold text-sm tracking-tight truncate transition-opacity duration-200"
                  :class="{ 'opacity-0 w-0': sidebarCollapsed, 'opacity-100': !sidebarCollapsed }">
                Reserva Litoral
            </span>
        </a>

        {{-- Botão colapsar (só desktop) --}}
        <button @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden lg:flex text-slate-400 hover:text-white transition rounded-lg p-1"
                :class="{ 'mx-auto': sidebarCollapsed }">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 transition-transform duration-300"
                 :class="{ 'rotate-180': sidebarCollapsed }"
                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    {{-- Nav Links --}}
    <nav class="flex-1 overflow-y-auto py-4 space-y-5 px-2">
        @foreach($nav as $sectionKey => $section)
            <div>
                {{-- Rótulo da seção (oculto quando colapsado) --}}
                <p class="px-3 mb-1 text-[10px] font-bold uppercase tracking-widest text-slate-500 transition-opacity duration-200"
                   :class="{ 'opacity-0 h-0 overflow-hidden mb-0': sidebarCollapsed, 'opacity-100': !sidebarCollapsed }">
                    {{ $section['label'] }}
                </p>

                <ul class="space-y-0.5">
                    @foreach($section['items'] as $item)
                        @php
                            $isActive = request()->routeIs($item['match']);
                            $isDisabled = $item['disabled'] ?? false;
                        @endphp

                        @if($isDisabled)
                            <li>
                                <span class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-slate-600 cursor-not-allowed opacity-50 group"
                                      title="{{ $item['label'] }}">
                                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        {!! $item['icon'] !!}
                                    </svg>
                                    <span class="transition-opacity duration-200 truncate"
                                          :class="{ 'opacity-0 w-0 overflow-hidden': sidebarCollapsed }">
                                        {{ $item['label'] }}
                                    </span>
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   title="{{ $item['label'] }}"
                                   class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-150 group relative"
                                   :class="{ 'justify-center': sidebarCollapsed }"
                                   @if($isActive)
                                       style="background: rgba(99,102,241,0.15);"
                                   @endif>

                                    {{-- Indicador active --}}
                                    @if($isActive)
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-indigo-400 rounded-r-full"></span>
                                    @endif

                                    <svg class="w-5 h-5 shrink-0 transition-colors {{ $isActive ? 'text-indigo-400' : 'text-slate-400 group-hover:text-white' }}"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        {!! $item['icon'] !!}
                                    </svg>

                                    <span class="transition-opacity duration-200 truncate {{ $isActive ? 'text-white' : 'text-slate-300 group-hover:text-white' }}"
                                          :class="{ 'opacity-0 w-0 overflow-hidden': sidebarCollapsed }">
                                        {{ $item['label'] }}
                                    </span>

                                    {{-- Tooltip quando colapsado --}}
                                    <span x-show="sidebarCollapsed"
                                          class="absolute left-14 z-50 whitespace-nowrap bg-slate-800 text-white text-xs font-semibold px-2.5 py-1.5 rounded-lg shadow-lg pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-150"
                                          style="display:none">
                                        {{ $item['label'] }}
                                    </span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @if(!$loop->last)
                <div class="border-t border-slate-800 mx-2"></div>
            @endif
        @endforeach
    </nav>

    {{-- Footer: User --}}
    <div class="shrink-0 border-t border-slate-800 p-3">
        <div class="flex items-center gap-3 rounded-xl px-2 py-2 hover:bg-slate-800 transition group cursor-default"
             :class="{ 'justify-center': sidebarCollapsed }">
            {{-- Avatar --}}
            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold shrink-0">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0 transition-opacity duration-200"
                 :class="{ 'opacity-0 w-0 overflow-hidden': sidebarCollapsed }">
                <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
            </div>
            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}" class="transition-opacity duration-200"
                  :class="{ 'opacity-0 w-0 overflow-hidden': sidebarCollapsed }">
                @csrf
                <button type="submit" title="Sair"
                        class="text-slate-500 hover:text-rose-400 transition p-1 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
