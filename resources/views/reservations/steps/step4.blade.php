<div x-show="step === 4" class="transition-opacity duration-300">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-sky-50 to-white px-6 py-5">
            <h3 class="text-lg font-semibold text-slate-900">Passo 4: Adicionar Extras</h3>
            <p class="mt-1 text-sm text-slate-500">Selecione serviços adicionais opcionais para a estadia.</p>
        </div>
        <div class="p-6 space-y-4">
            <div class="divide-y divide-slate-100">
                
                <!-- Café da manhã -->
                <label class="flex items-start gap-4 py-4 cursor-pointer select-none">
                    <input type="checkbox" value="cafe" x-model="extras"
                           class="mt-1 h-5 w-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <div class="flex-1">
                        <div class="flex justify-between items-baseline">
                            <span class="font-bold text-slate-800">Café da Manhã</span>
                            <span class="text-sm font-semibold text-slate-500">R$ 40,00 / pessoa / noite</span>
                        </div>
                        <p class="text-sm text-slate-500 mt-1">Refeição matinal completa servida no restaurante.</p>
                        <template x-if="extras.includes('cafe')">
                            <p class="text-xs text-sky-600 font-semibold mt-2"
                               x-text="'Total: R$ ' + (nights * adults * 40.00).toLocaleString('pt-BR', {minimumFractionDigits: 2}) + ' (' + (nights * adults) + ' cafés no total)'"></p>
                        </template>
                    </div>
                </label>

                <!-- Estacionamento -->
                <label class="flex items-start gap-4 py-4 cursor-pointer select-none">
                    <input type="checkbox" value="estacionamento" x-model="extras"
                           class="mt-1 h-5 w-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <div class="flex-1">
                        <div class="flex justify-between items-baseline">
                            <span class="font-bold text-slate-800">Estacionamento</span>
                            <span class="text-sm font-semibold text-slate-500">R$ 30,00 / noite</span>
                        </div>
                        <p class="text-sm text-slate-500 mt-1">Vaga coberta e com segurança 24h para um veículo.</p>
                        <template x-if="extras.includes('estacionamento')">
                            <p class="text-xs text-sky-600 font-semibold mt-2"
                               x-text="'Total: R$ ' + (nights * 30.00).toLocaleString('pt-BR', {minimumFractionDigits: 2})"></p>
                        </template>
                    </div>
                </label>

                <!-- Berço -->
                <label class="flex items-start gap-4 py-4 cursor-pointer select-none">
                    <input type="checkbox" value="berco" x-model="extras"
                           class="mt-1 h-5 w-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <div class="flex-1">
                        <div class="flex justify-between items-baseline">
                            <span class="font-bold text-slate-800">Aluguel de Berço</span>
                            <span class="text-sm font-semibold text-slate-500">R$ 50,00 (taxa única)</span>
                        </div>
                        <p class="text-sm text-slate-500 mt-1">Instalação de berço infantil confortável no quarto.</p>
                        <template x-if="extras.includes('berco')">
                            <p class="text-xs text-sky-600 font-semibold mt-2">Total: R$ 50,00</p>
                        </template>
                    </div>
                </label>

                <!-- Pet -->
                <label class="flex items-start gap-4 py-4 cursor-pointer select-none">
                    <input type="checkbox" value="pet" x-model="extras"
                           class="mt-1 h-5 w-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <div class="flex-1">
                        <div class="flex justify-between items-baseline">
                            <span class="font-bold text-slate-800">Taxa de Pet</span>
                            <span class="text-sm font-semibold text-slate-500">R$ 80,00 (taxa única)</span>
                        </div>
                        <p class="text-sm text-slate-500 mt-1">Hospedagem pet-friendly (aceitamos animais de até 15kg).</p>
                        <template x-if="extras.includes('pet')">
                            <p class="text-xs text-sky-600 font-semibold mt-2">Total: R$ 80,00</p>
                        </template>
                    </div>
                </label>

                <!-- Cama extra -->
                <label class="flex items-start gap-4 py-4 cursor-pointer select-none">
                    <input type="checkbox" value="cama_extra" x-model="extras"
                           class="mt-1 h-5 w-5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <div class="flex-1">
                        <div class="flex justify-between items-baseline">
                            <span class="font-bold text-slate-800">Cama Extra</span>
                            <span class="text-sm font-semibold text-slate-500">R$ 120,00 (taxa única)</span>
                        </div>
                        <p class="text-sm text-slate-500 mt-1">Inclusão de uma cama de solteiro adicional no quarto.</p>
                        <template x-if="extras.includes('cama_extra')">
                            <p class="text-xs text-sky-600 font-semibold mt-2">Total: R$ 120,00</p>
                        </template>
                    </div>
                </label>

            </div>
        </div>
    </div>

    <!-- Navegação do Passo -->
    <div class="mt-8 flex justify-between border-t border-slate-100 pt-5">
        <button type="button" @click="step = 3"
                class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
            Voltar
        </button>
        <button type="button" @click="step = 5"
                class="rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
            Avançar
        </button>
    </div>
</div>
