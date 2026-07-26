<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Novo Hóspede" subtitle="Cadastre um novo hóspede no sistema do hotel" :backUrl="route('guests.index')" />
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-message />

            <x-form-section title="Dados Pessoais e Contato">
                <form action="{{ route('guests.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nome Completo --}}
                        <div class="md:col-span-2">
                            <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nome Completo <span class="text-rose-500">*</span></label>
                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required autofocus
                                   placeholder="Nome completo do hóspede..."
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('full_name') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('full_name')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- E-mail --}}
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">E-mail</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   placeholder="email@exemplo.com"
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('email') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('email')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Telefone --}}
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Telefone</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                   placeholder="(00) 00000-0000"
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('phone') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('phone')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tipo de Documento --}}
                        <div>
                            <label for="document_type" class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Documento</label>
                            <select name="document_type" id="document_type"
                                    class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('document_type') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                                <option value="">Selecione...</option>
                                <option value="CPF" {{ old('document_type') === 'CPF' ? 'selected' : '' }}>CPF</option>
                                <option value="RG" {{ old('document_type') === 'RG' ? 'selected' : '' }}>RG</option>
                                <option value="Passaporte" {{ old('document_type') === 'Passaporte' ? 'selected' : '' }}>Passaporte</option>
                                <option value="CNPJ" {{ old('document_type') === 'CNPJ' ? 'selected' : '' }}>CNPJ</option>
                                <option value="Outro" {{ old('document_type') === 'Outro' ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('document_type')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Número do Documento --}}
                        <div>
                            <label for="document_number" class="block text-sm font-semibold text-slate-700 mb-1.5">Número do Documento</label>
                            <input type="text" name="document_number" id="document_number" value="{{ old('document_number') }}"
                                   placeholder="Apenas números ou formato do doc..."
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('document_number') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('document_number')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Data de Nascimento --}}
                        <div>
                            <label for="birth_date" class="block text-sm font-semibold text-slate-700 mb-1.5">Data de Nascimento</label>
                            <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}"
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('birth_date') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('birth_date')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nacionalidade --}}
                        <div>
                            <label for="nationality" class="block text-sm font-semibold text-slate-700 mb-1.5">País / Nacionalidade</label>
                            <input type="text" name="nationality" id="nationality" value="{{ old('nationality', 'BR') }}" maxlength="2"
                                   placeholder="BR, US, ES..."
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('nationality') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('nationality')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Observações --}}
                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Observações (Opcional)</label>
                            <textarea name="notes" id="notes" rows="4" placeholder="Preferências, restrições alimentares, perfil..."
                                      class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('notes') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-50 pt-6">
                        <a href="{{ route('guests.index') }}"
                           class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Cadastrar Hóspede
                        </button>
                    </div>
                </form>
            </x-form-section>

        </div>
    </div>
</x-app-layout>
