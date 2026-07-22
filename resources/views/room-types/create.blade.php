<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('room-types.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Nova Categoria de Quarto</h2>
                <p class="text-sm text-gray-500 mt-1">Crie um novo tipo de acomodação para o seu hotel</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50">
                    <h3 class="font-bold text-slate-800">Detalhes da Acomodação</h3>
                </div>

                <form action="{{ route('room-types.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nome da Categoria --}}
                        <div class="md:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nome da Categoria</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   placeholder="Ex: Suíte Luxo Vista Mar, Quarto Standard Casal..."
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('name') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('name')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Capacidade Base --}}
                        <div>
                            <label for="base_capacity" class="block text-sm font-semibold text-slate-700 mb-1.5">Capacidade Base (Adultos)</label>
                            <input type="number" name="base_capacity" id="base_capacity" value="{{ old('base_capacity', 2) }}" min="1" required
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('base_capacity') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('base_capacity')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Capacidade Máxima --}}
                        <div>
                            <label for="max_capacity" class="block text-sm font-semibold text-slate-700 mb-1.5">Capacidade Máxima (Total)</label>
                            <input type="number" name="max_capacity" id="max_capacity" value="{{ old('max_capacity', 2) }}" min="1" required
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('max_capacity') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('max_capacity')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Preço Base --}}
                        <div>
                            <label for="base_price" class="block text-sm font-semibold text-slate-700 mb-1.5">Preço Base Diária (R$)</label>
                            <input type="number" name="base_price" id="base_price" value="{{ old('base_price', 0.00) }}" step="0.01" min="0" required
                                   placeholder="0,00"
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('base_price') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('base_price')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Descrição --}}
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">Descrição Comercial</label>
                            <textarea name="description" id="description" rows="5" placeholder="Escreva os detalhes, diferenciais e informações sobre esta acomodação para os hóspedes..."
                                      class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('description') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-50 pt-6">
                        <a href="{{ route('room-types.index') }}"
                           class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                            Continuar Cadastro →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
