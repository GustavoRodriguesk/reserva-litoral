<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Comodidades dos Quartos" subtitle="Gerencie as comodidades (diferenciais, facilidades, etc.) que podem ser associadas às categorias de quartos." />
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-message />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Listagem de Comodidades --}}
                <div class="lg:col-span-2 space-y-6">
                    <x-table>
                        <x-slot name="header">
                            <h3 class="font-bold text-slate-800">Comodidades Cadastradas</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Lista de todas as comodidades globais do sistema</p>
                        </x-slot>

                        <x-slot name="head">
                            <th class="px-6 py-3.5 w-16 text-center">Ícone</th>
                            <th class="px-6 py-3.5">Nome</th>
                            <th class="px-6 py-3.5">Identificador de Ícone</th>
                            <th class="px-6 py-3.5 text-right">Ações</th>
                        </x-slot>

                        @if($amenities->isEmpty())
                            <tr>
                                <td colspan="4">
                                    <x-empty-state title="Nenhuma comodidade cadastrada" description="Use o formulário ao lado para cadastrar itens como Wi-Fi, Ar Condicionado, Frigobar, etc.">
                                        <x-slot name="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21L14.907 18M18 10.5c0 4.142-3.858 7.5-8.625 7.5c-1.442 0-2.8-.307-4.01-.849L3 18l1.326-3.978C3.58 12.87 3 11.233 3 9.5C3 5.358 6.858 2 11.625 2c4.27 0 7.82 2.695 8.442 6.275c.148.854.238 1.764.238 2.725z" /></svg>
                                        </x-slot>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @else
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
                        @endif
                    </x-table>
                </div>

                {{-- Cadastrar Nova Comodidade --}}
                <div>
                    <x-form-section title="Nova Comodidade">
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
                    </x-form-section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
