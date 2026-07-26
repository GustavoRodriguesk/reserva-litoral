<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Hóspedes" subtitle="Gerencie os dados cadastrais dos hóspedes do hotel">
            <x-slot name="actions">
                <a href="{{ route('guests.create') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Novo Hóspede
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <x-flash-message />

            {{-- Filtros --}}
            <form method="GET" action="{{ route('guests.index') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[250px]">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wide">Buscar Hóspede</label>
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <input type="text" name="search" value="{{ $search ?? '' }}"
                                   placeholder="Nome, e-mail, documento ou telefone..."
                                   class="block w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            Buscar
                        </button>
                        @if(!empty($search))
                            <a href="{{ route('guests.index') }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl text-sm transition">
                                Limpar
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                <x-table>
                    <x-slot name="header">
                        <h3 class="font-bold text-slate-800">Listagem de Hóspedes</h3>
                    </x-slot>

                    <x-slot name="head">
                        <th class="px-6 py-3.5">Nome</th>
                        <th class="px-6 py-3.5">E-mail</th>
                        <th class="px-6 py-3.5">Documento</th>
                        <th class="px-6 py-3.5">Telefone</th>
                        <th class="px-6 py-3.5 text-right">Ações</th>
                    </x-slot>

                    @if($guests->isEmpty())
                        <tr>
                            <td colspan="5">
                                <x-empty-state title="Nenhum hóspede encontrado" description="Você ainda não possui hóspedes cadastrados ou sua busca não retornou resultados.">
                                    <x-slot name="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                                    </x-slot>
                                    <x-slot name="action">
                                        <a href="{{ route('guests.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition">
                                            Cadastrar Hóspede
                                        </a>
                                    </x-slot>
                                </x-empty-state>
                            </td>
                        </tr>
                    @else
                        @foreach($guests as $guest)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $guest->full_name }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-sm">
                                    {{ $guest->email ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-sm">
                                    {{ $guest->document_type ? $guest->document_type.': ' : '' }}{{ $guest->document_number ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-slate-500 text-sm">
                                    {{ $guest->phone ?: '-' }}
                                </td>
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('guests.show', $guest) }}" class="text-slate-600 hover:text-slate-900 font-semibold text-xs transition">
                                            Ver Perfil
                                        </a>
                                        <a href="{{ route('guests.edit', $guest) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs transition">
                                            Editar
                                        </a>
                                        <form action="{{ route('guests.destroy', $guest) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir este hóspede?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-900 font-semibold text-xs transition">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </x-table>

                @if($guests->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $guests->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
