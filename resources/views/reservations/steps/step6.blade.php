<div x-show="step === 6" class="transition-opacity duration-300">
    <div class="grid gap-6 lg:grid-cols-3">
        
        <!-- Detalhes do Resumo (Coluna Dupla) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-sky-50 to-white px-6 py-5">
                    <h3 class="text-lg font-semibold text-slate-900">Passo 6: Confirme os Dados da Reserva</h3>
                    <p class="mt-1 text-sm text-slate-500">Revise as informações antes de finalizar a criação.</p>
                </div>
                <div class="p-6 divide-y divide-slate-100 space-y-6">
                    
                    <!-- Hóspede -->
                    <div class="pt-0">
                        <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-3">Hóspede Principal</h4>
                        <template x-if="selectedGuest">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">👤</span>
                                <div>
                                    <p class="font-bold text-slate-800" x-text="selectedGuest.full_name"></p>
                                    <p class="text-sm text-slate-500" x-text="selectedGuest.email || 'Sem e-mail'"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Quarto e Datas -->
                    <div class="pt-6">
                        <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-3">Acomodação e Período</h4>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <template x-if="selectedRoom">
                                <div>
                                    <p class="text-xs text-slate-400 font-semibold">Quarto</p>
                                    <p class="font-bold text-slate-800 mt-1" x-text="'Quarto ' + selectedRoom.number"></p>
                                    <p class="text-sm text-slate-500" x-text="selectedRoom.room_type_name"></p>
                                </div>
                            </template>
                            <div>
                                <p class="text-xs text-slate-400 font-semibold">Estadia</p>
                                <p class="font-bold text-slate-800 mt-1">
                                    <span x-text="new Date(check_in + 'T12:00:00').toLocaleDateString('pt-BR')"></span>
                                    <span> a </span>
                                    <span x-text="new Date(check_out + 'T12:00:00').toLocaleDateString('pt-BR')"></span>
                                </p>
                                <p class="text-sm text-slate-500" x-text="nights + (nights === 1 ? ' diária' : ' diárias') + ' · ' + adults + ' ad. / ' + children + ' cri.'"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="pt-6">
                        <label for="notes" class="block text-sm font-semibold text-slate-700">Observações Internas (Opcional)</label>
                        <textarea id="notes" x-model="notes" rows="3" placeholder="Ex: Hóspede solicitou cama extra, restrições alimentares, etc."
                                  class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"></textarea>
                    </div>

                </div>
            </div>
        </div>

        <!-- Coluna Financeira -->
        <div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 text-white shadow-sm">
                <div class="bg-slate-950 px-6 py-5 border-b border-slate-800">
                    <h3 class="font-bold">Resumo Financeiro</h3>
                </div>
                <div class="p-6 space-y-4">
                    <!-- Diárias -->
                    <div class="flex justify-between text-sm text-slate-400">
                        <span x-text="'Diárias (' + nights + 'x)'"></span>
                        <span class="text-slate-200" x-text="'R$ ' + parseFloat(subtotal).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                    </div>

                    <!-- Extras Lista -->
                    <template x-if="extras.length > 0">
                        <div class="space-y-2 border-t border-slate-800 pt-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Serviços Adicionais</p>
                            <template x-if="extras.includes('cafe')">
                                <div class="flex justify-between text-xs text-slate-400">
                                    <span>Café da Manhã</span>
                                    <span x-text="'R$ ' + (nights * adults * 40.00).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                                </div>
                            </template>
                            <template x-if="extras.includes('estacionamento')">
                                <div class="flex justify-between text-xs text-slate-400">
                                    <span>Estacionamento</span>
                                    <span x-text="'R$ ' + (nights * 30.00).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></span>
                                </div>
                            </template>
                            <template x-if="extras.includes('berco')">
                                <div class="flex justify-between text-xs text-slate-400">
                                    <span>Aluguel de Berço</span>
                                    <span>R$ 50,00</span>
                                </div>
                            </template>
                            <template x-if="extras.includes('pet')">
                                <div class="flex justify-between text-xs text-slate-400">
                                    <span>Taxa de Pet</span>
                                    <span>R$ 80,00</span>
                                </div>
                            </template>
                            <template x-if="extras.includes('cama_extra')">
                                <div class="flex justify-between text-xs text-slate-400">
                                    <span>Cama Extra</span>
                                    <span>R$ 120,00</span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Total -->
                    <div class="border-t border-slate-800 pt-4">
                        <div class="flex justify-between text-base font-bold">
                            <span>Valor Total</span>
                            <span class="text-sky-400 text-lg" x-text="'R$ ' + parseFloat(grandTotal).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </div>
                    </div>

                    <!-- Forma de pagamento final -->
                    <div class="bg-slate-950/40 rounded-xl p-4 border border-slate-800 text-xs text-slate-400 space-y-1">
                        <span class="font-bold text-slate-300 block">Pagamento Inicial</span>
                        <span x-show="paymentMethod === 'pending'">Pendente — A ser pago na entrada/check-in</span>
                        <span x-show="paymentMethod === 'pix'">Integral — Registrado como PAGO via PIX</span>
                        <span x-show="paymentMethod === 'credit_card'">Integral — Registrado como PAGO via Cartão</span>
                        <span x-show="paymentMethod === 'cash'">Integral — Registrado como PAGO via Dinheiro</span>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- Navegação do Passo -->
    <div class="mt-8 flex justify-between border-t border-slate-100 pt-5">
        <button type="button" @click="step = 5"
                class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
            Voltar
        </button>
        <button type="button" @click="submitReservation()" :disabled="savingReservation"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-3 text-base font-bold text-white shadow-md hover:bg-emerald-700 transition disabled:opacity-50 active:scale-98">
            <template x-if="savingReservation">
                <svg class="h-5 w-5 animate-spin text-white mr-1" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </template>
            Confirmar e Salvar Reserva
        </button>
    </div>
</div>
