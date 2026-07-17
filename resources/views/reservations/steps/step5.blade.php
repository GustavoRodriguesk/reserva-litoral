<div x-show="step === 5" class="transition-opacity duration-300">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-sky-50 to-white px-6 py-5">
            <h3 class="text-lg font-semibold text-slate-900">Passo 5: Método de Pagamento Inicial</h3>
            <p class="mt-1 text-sm text-slate-500">Selecione como será quitado o valor inicial ou escolha registrar como pendente.</p>
        </div>
        <div class="p-6">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                
                <!-- PIX -->
                <label class="flex flex-col items-center justify-center border-2 rounded-2xl p-5 cursor-pointer text-center select-none transition"
                       :class="paymentMethod === 'pix' ? 'border-sky-500 bg-sky-50/30' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="payment_method_input" value="pix" x-model="paymentMethod" class="sr-only">
                    <span class="text-3xl">📱</span>
                    <span class="font-bold text-slate-800 mt-2 block">PIX</span>
                    <span class="text-xs text-slate-500 mt-1 block">Confirmação instantânea</span>
                </label>

                <!-- Cartão -->
                <label class="flex flex-col items-center justify-center border-2 rounded-2xl p-5 cursor-pointer text-center select-none transition"
                       :class="paymentMethod === 'credit_card' ? 'border-sky-500 bg-sky-50/30' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="payment_method_input" value="credit_card" x-model="paymentMethod" class="sr-only">
                    <span class="text-3xl">💳</span>
                    <span class="font-bold text-slate-800 mt-2 block">Cartão</span>
                    <span class="text-xs text-slate-500 mt-1 block">Crédito ou débito</span>
                </label>

                <!-- Dinheiro -->
                <label class="flex flex-col items-center justify-center border-2 rounded-2xl p-5 cursor-pointer text-center select-none transition"
                       :class="paymentMethod === 'cash' ? 'border-sky-500 bg-sky-50/30' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="payment_method_input" value="cash" x-model="paymentMethod" class="sr-only">
                    <span class="text-3xl">💵</span>
                    <span class="font-bold text-slate-800 mt-2 block">Dinheiro</span>
                    <span class="text-xs text-slate-500 mt-1 block">Pago no balcão</span>
                </label>

                <!-- Pendente -->
                <label class="flex flex-col items-center justify-center border-2 rounded-2xl p-5 cursor-pointer text-center select-none transition"
                       :class="paymentMethod === 'pending' ? 'border-sky-500 bg-sky-50/30' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="payment_method_input" value="pending" x-model="paymentMethod" class="sr-only">
                    <span class="text-3xl">⏳</span>
                    <span class="font-bold text-slate-800 mt-2 block">A pagar / Pendente</span>
                    <span class="text-xs text-slate-500 mt-1 block">Quitar no check-in</span>
                </label>

            </div>
        </div>
    </div>

    <!-- Navegação do Passo -->
    <div class="mt-8 flex justify-between border-t border-slate-100 pt-5">
        <button type="button" @click="step = 4"
                class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
            Voltar
        </button>
        <button type="button" @click="step = 6"
                class="rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
            Avançar
        </button>
    </div>
</div>
