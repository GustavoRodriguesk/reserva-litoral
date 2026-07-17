@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Nome Completo -->
    <div>
        <x-input-label for="full_name" :value="__('Nome Completo *')" />
        <x-text-input id="full_name" class="block mt-1 w-full" type="text" name="full_name" :value="old('full_name', $guest->full_name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
    </div>

    <!-- E-mail -->
    <div>
        <x-input-label for="email" :value="__('E-mail')" />
        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $guest->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- Telefone -->
    <div>
        <x-input-label for="phone" :value="__('Telefone')" />
        <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $guest->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <!-- Data de Nascimento -->
    <div>
        <x-input-label for="birth_date" :value="__('Data de Nascimento')" />
        <x-text-input id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" :value="old('birth_date', isset($guest->birth_date) ? $guest->birth_date->format('Y-m-d') : '')" />
        <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
    </div>

    <!-- Tipo de Documento -->
    <div>
        <x-input-label for="document_type" :value="__('Tipo de Documento')" />
        <select id="document_type" name="document_type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
            <option value="">Selecione...</option>
            <option value="CPF" {{ old('document_type', $guest->document_type ?? '') === 'CPF' ? 'selected' : '' }}>CPF</option>
            <option value="RG" {{ old('document_type', $guest->document_type ?? '') === 'RG' ? 'selected' : '' }}>RG</option>
            <option value="Passaporte" {{ old('document_type', $guest->document_type ?? '') === 'Passaporte' ? 'selected' : '' }}>Passaporte</option>
            <option value="CNPJ" {{ old('document_type', $guest->document_type ?? '') === 'CNPJ' ? 'selected' : '' }}>CNPJ</option>
            <option value="Outro" {{ old('document_type', $guest->document_type ?? '') === 'Outro' ? 'selected' : '' }}>Outro</option>
        </select>
        <x-input-error :messages="$errors->get('document_type')" class="mt-2" />
    </div>

    <!-- Número do Documento -->
    <div>
        <x-input-label for="document_number" :value="__('Número do Documento')" />
        <x-text-input id="document_number" class="block mt-1 w-full" type="text" name="document_number" :value="old('document_number', $guest->document_number ?? '')" />
        <x-input-error :messages="$errors->get('document_number')" class="mt-2" />
    </div>
    
    <!-- Nacionalidade -->
    <div>
        <x-input-label for="nationality" :value="__('Nacionalidade (Código do País)')" />
        <x-text-input id="nationality" class="block mt-1 w-full" type="text" name="nationality" :value="old('nationality', $guest->nationality ?? 'BR')" maxlength="2" />
        <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
    </div>
    
    <!-- Idioma Preferido -->
    <div>
        <x-input-label for="preferred_language" :value="__('Idioma Preferido')" />
        <x-text-input id="preferred_language" class="block mt-1 w-full" type="text" name="preferred_language" :value="old('preferred_language', $guest->preferred_language ?? 'pt-BR')" maxlength="10" />
        <x-input-error :messages="$errors->get('preferred_language')" class="mt-2" />
    </div>
    
    <!-- Observações -->
    <div class="md:col-span-2">
        <x-input-label for="notes" :value="__('Observações')" />
        <textarea id="notes" name="notes" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" rows="3">{{ old('notes', $guest->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>
