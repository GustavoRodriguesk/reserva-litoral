<div x-show="step === 1" class="transition-opacity duration-300">
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-sky-50 to-white px-6 py-5">
            <h3 class="text-lg font-semibold text-slate-900">Passo 1: Datas e Hóspedes</h3>
            <p class="mt-1 text-sm text-slate-500">Informe o período e a quantidade de hóspedes para consultar os quartos livres.</p>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Check-in -->
                <div>
                    <label for="check_in" class="mb-2 block text-sm font-semibold text-slate-700">Check-in</label>
                    <input id="check_in" type="date" x-model="check_in" :min="new Date().toISOString().split('T')[0]"
                           class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500" required>
                </div>

                <!-- Check-out -->
                <div>
                    <label for="check_out" class="mb-2 block text-sm font-semibold text-slate-700">Check-out</label>
                    <input id="check_out" type="date" x-model="check_out" :min="check_in ? check_in : new Date().toISOString().split('T')[0]"
                           class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500" required>
                </div>

                <!-- Adults Control -->
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Adultos</label>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="if(adults > 1) adults--"
                                class="flex h-10 w-10 items-center justify-center rounded-l-lg border border-slate-300 bg-slate-50 text-slate-600 hover:bg-slate-100 transition active:scale-95">
                            <span class="text-xl font-bold">−</span>
                        </button>
                        <div class="flex h-10 flex-1 items-center justify-center border-y border-slate-300 text-sm font-bold text-slate-800" x-text="adults"></div>
                        <button type="button" @click="adults++"
                                class="flex h-10 w-10 items-center justify-center rounded-r-lg border border-slate-300 bg-slate-50 text-slate-600 hover:bg-slate-100 transition active:scale-95">
                            <span class="text-xl font-bold">+</span>
                        </button>
                    </div>
                </div>

                <!-- Children Control -->
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Crianças</label>
                    <div class="flex items-center gap-1">
                        <button type="button" @click="if(children > 0) children--"
                                class="flex h-10 w-10 items-center justify-center rounded-l-lg border border-slate-300 bg-slate-50 text-slate-600 hover:bg-slate-100 transition active:scale-95">
                            <span class="text-xl font-bold">−</span>
                        </button>
                        <div class="flex h-10 flex-1 items-center justify-center border-y border-slate-300 text-sm font-bold text-slate-800" x-text="children"></div>
                        <button type="button" @click="children++"
                                class="flex h-10 w-10 items-center justify-center rounded-r-lg border border-slate-300 bg-slate-50 text-slate-600 hover:bg-slate-100 transition active:scale-95">
                            <span class="text-xl font-bold">+</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-100 pt-5">
                <button type="button" @click="searchRooms()" :disabled="!check_in || !check_out || loadingRooms"
                        class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 disabled:opacity-50">
                    <template x-if="loadingRooms">
                        <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    Buscar disponibilidade
                </button>
            </div>
        </div>
    </div>
</div>
