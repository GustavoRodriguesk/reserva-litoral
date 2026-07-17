<div x-show="step === 3" class="transition-opacity duration-300">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <!-- Header -->
        <div class="border-b border-slate-100 bg-gradient-to-r from-sky-50 to-white px-6 py-5 flex justify-between items-center flex-wrap gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Passo 3: Selecione o Hóspede Principal</h3>
                <p class="mt-1 text-sm text-slate-500">Selecione o responsável financeiro e legal pela hospedagem.</p>
            </div>
            <button type="button" @click="showNewGuestModal = true"
                    class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                + Novo Hóspede
            </button>
        </div>

        <div class="p-6 space-y-6">
            <!-- Hóspede Selecionado Card -->
            <template x-if="selectedGuest">
                <div class="rounded-xl border border-sky-200 bg-sky-50/50 p-5 flex items-center justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-100 text-xl text-sky-700">👤</div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-sky-600">Hóspede Selecionado</p>
                            <h4 class="text-lg font-bold text-slate-900 mt-0.5" x-text="selectedGuest.full_name"></h4>
                            <div class="mt-1 text-sm text-slate-600 flex flex-wrap gap-x-4 gap-y-1">
                                <span x-show="selectedGuest.email" x-text="'✉ ' + selectedGuest.email"></span>
                                <span x-show="selectedGuest.phone" x-text="'📞 ' + selectedGuest.phone"></span>
                                <span x-show="selectedGuest.document_number" x-text="'🪪 ' + (selectedGuest.document_type || 'Documento') + ': ' + selectedGuest.document_number"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="selectedGuest = null"
                            class="text-sm font-semibold text-rose-600 hover:text-rose-800 transition">
                        Remover
                    </button>
                </div>
            </template>

            <!-- Campo de Busca -->
            <div class="space-y-4" x-show="!selectedGuest">
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <input type="text" x-model="searchGuestQuery" @keyup.enter="searchGuests()"
                               placeholder="Digite nome, e-mail, telefone ou documento para pesquisar..."
                               class="block w-full rounded-lg border-slate-300 pl-4 pr-10 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                    </div>
                    <button type="button" @click="searchGuests()" :disabled="loadingGuests"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                        <template x-if="loadingGuests">
                            <svg class="h-4 w-4 animate-spin text-slate-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        Pesquisar
                    </button>
                </div>

                <!-- Tabela de Resultados -->
                <div class="overflow-x-auto rounded-xl border border-slate-100 bg-white" x-show="guests.length > 0">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nome</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Documento</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Contato</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="guest in guests" :key="guest.id">
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-slate-900" x-text="guest.full_name"></td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">
                                        <span x-text="guest.document_type ? guest.document_type + ': ' : ''"></span>
                                        <span x-text="guest.document_number || '-'"></span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">
                                        <div x-text="guest.email || '-'"></div>
                                        <div class="text-xs text-slate-400 mt-0.5" x-text="guest.phone || ''"></div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right text-sm">
                                        <button type="button" @click="selectedGuest = guest"
                                                class="rounded-lg bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-600 hover:text-white transition">
                                            Selecionar
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegação do Passo -->
    <div class="mt-8 flex justify-between border-t border-slate-100 pt-5">
        <button type="button" @click="step = 2"
                class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
            Voltar
        </button>
        <button type="button" @click="step = 4" :disabled="!selectedGuest"
                class="rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 disabled:opacity-50">
            Avançar
        </button>
    </div>

    <!-- Modal Novo Hóspede -->
    <div x-show="showNewGuestModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" @click="showNewGuestModal = false"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Modal Box -->
            <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-6 pt-6 pb-4 sm:pb-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-5">Cadastrar Novo Hóspede</h3>
                    
                    <div class="space-y-4">
                        <!-- Nome -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Nome Completo *</label>
                            <input type="text" x-model="newGuest.full_name" required
                                   class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                            <span class="text-xs text-rose-600" x-show="guestErrors.full_name" x-text="guestErrors.full_name ? guestErrors.full_name[0] : ''"></span>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">E-mail</label>
                            <input type="email" x-model="newGuest.email"
                                   class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                            <span class="text-xs text-rose-600" x-show="guestErrors.email" x-text="guestErrors.email ? guestErrors.email[0] : ''"></span>
                        </div>

                        <!-- Telefone -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Telefone</label>
                            <input type="text" x-model="newGuest.phone"
                                   class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                            <span class="text-xs text-rose-600" x-show="guestErrors.phone" x-text="guestErrors.phone ? guestErrors.phone[0] : ''"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Tipo de Documento -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tipo Documento</label>
                                <select x-model="newGuest.document_type"
                                        class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                                    <option value="CPF">CPF</option>
                                    <option value="RG">RG</option>
                                    <option value="Passaporte">Passaporte</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>

                            <!-- Número do Documento -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Número</label>
                                <input type="text" x-model="newGuest.document_number"
                                       class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                                <span class="text-xs text-rose-600" x-show="guestErrors.document_number" x-text="guestErrors.document_number ? guestErrors.document_number[0] : ''"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3">
                    <button type="button" @click="saveGuest()" :disabled="savingGuest"
                            class="inline-flex justify-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 transition">
                        <template x-if="savingGuest">
                            <svg class="h-4 w-4 animate-spin text-white mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        Salvar Hóspede
                    </button>
                    <button type="button" @click="showNewGuestModal = false"
                            class="inline-flex justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
