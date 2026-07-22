<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            Comodidades dos Quartos
        </h2>
        <p class="text-sm text-gray-500 mt-1">Gerencie as comodidades (comodidades, diferenciais, etc.) que podem ser associadas às categorias de quartos.</p>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl">
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Listagem de Comodidades --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50">
                            <h3 class="font-bold text-slate-800">Comodidades Cadastradas</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Lista de todas as comodidades globais do sistema</p>
                        </div>

                        @if($amenities->isEmpty())
                            <div class="p-12 text-center">
                                <div class="mx-auto w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21L14.907 18M18 10.5c0 4.142-3.858 7.5-8.625 7.5c-1.442 0-2.8-.307-4.01-.849L3 18l1.326-3.978C3.58 12.87 3 11.233 3 9.5C3 5.358 6.858 2 11.625 2c4.27 0 7.82 2.695 8.442 6.275c.148.854.238 1.764.238 2.725z" /></svg>
                                </div>
                                <p class="text-slate-500 font-semibold text-sm">Nenhuma comodidade cadastrada</p>
                                <p class="text-slate-400 text-xs mt-0.5">Use o formulário ao lado para cadastrar itens como Wi-Fi, Ar Condicionado, Frigobar, etc.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                                            <th class="px-6 py-3.5 w-16 text-center">Ícone</th>
                                            <th class="px-6 py-3.5">Nome</th>
                                            <th class="px-6 py-3.5">Identificador de Ícone</th>
                                            <th class="px-6 py-3.5 text-right">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50 text-sm">
                                        @foreach($amenities as $amenity)
                                            <tr class="hover:bg-slate-50/50 transition">
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 items-center justify-center font-bold text-xs uppercase">
                                                        {{ substr($amenity->name, 0, 2) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 font-semibold text-slate-800">
                                                    {{ $amenity->name }}
                                                </td>
                                                <td class="px-6 py-4 text-slate-500 font-mono text-xs">
                                                    {{ $amenity->icon ?? '-' }}
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <form action="{{ route('amenities.destroy', $amenity) }}" method="POST" class="inline" onsubmit="return confirm('Deseja realmente remover esta comodidade? Isso irá desassociá-la de todos os tipos de quarto.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-rose-600 hover:text-rose-900 font-semibold text-xs transition">
                                                            Excluir
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Cadastrar Nova Comodidade --}}
                <div>
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <h3 class="font-bold text-slate-800">Nova Comodidade</h3>
                        <form action="{{ route('amenities.store') }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nome da Comodidade</label>
                                <input type="text" name="name" id="name" required placeholder="Ex: Wi-Fi, Ar Condicionado..."
                                       class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            </div>

                            <div>
                                <label for="icon" class="block text-sm font-semibold text-slate-700 mb-1.5">Ícone (Símbolo/Slug)</label>
                                <select name="icon" id="icon" class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                    <option value="wifi">wifi (Wi-Fi)</option>
                                    <option value="tv">tv (TV/Cabo)</option>
                                    <option value="wind">wind (Ar Condicionado)</option>
                                    <option value="coffee">coffee (Cafeteira/Café da manhã)</option>
                                    <option value="water">water (Chuveiro/Piscina)</option>
                                    <option value="key">key (Cofre/Acesso)</option>
                                    <option value="utensils">utensils (Cozinha/Refeições)</option>
                                    <option value="car">car (Estacionamento)</option>
                                    <option value="shield">shield (Segurança)</option>
                                    <option value="briefcase">briefcase (Espaço de Trabalho)</option>
                                    <option value="heart">heart (Pet Friendly)</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Adicionar Comodidade
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
