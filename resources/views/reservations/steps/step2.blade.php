<div x-show="step === 2" class="transition-opacity duration-300">
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h3 class="text-xl font-bold text-slate-900">Passo 2: Selecione o Quarto</h3>
            <p class="mt-1 text-sm text-slate-500">Escolha a acomodação ideal para a estadia.</p>
        </div>
        <span class="inline-flex w-fit rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-800"
              x-text="rooms.length + (rooms.length === 1 ? ' opção encontrada' : ' opções encontradas')"></span>
    </div>

    <!-- Lista de Quartos -->
    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <template x-for="room in rooms" :key="room.id">
            <article class="flex flex-col overflow-hidden rounded-2xl border bg-white shadow-sm transition hover:shadow-md cursor-pointer"
                     :class="selectedRoom && selectedRoom.id === room.id ? 'border-sky-500 ring-2 ring-sky-100' : 'border-slate-200'"
                     @click="selectedRoom = room">
                <!-- Cabeçalho do Card -->
                <div class="flex items-center justify-between px-5 py-4 text-white transition"
                     :class="selectedRoom && selectedRoom.id === room.id ? 'bg-sky-600' : 'bg-slate-900'">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider opacity-85">Quarto</p>
                        <p class="text-2xl font-bold" x-text="room.number"></p>
                    </div>
                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold"
                          x-text="selectedRoom && selectedRoom.id === room.id ? 'Selecionado' : 'Disponível'"></span>
                </div>
                
                <!-- Corpo do Card -->
                <div class="flex flex-1 flex-col p-5">
                    <h4 class="text-lg font-bold text-slate-900" x-text="room.room_type_name"></h4>
                    <p class="mt-2 text-sm text-slate-500" x-text="'Capacidade de até ' + room.max_capacity + ' hóspedes'"></p>
                    
                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                            Valor sugerido por diária
                        </p>
                        <p class="mt-1 text-xl font-bold text-slate-800" x-text="'R$ ' + parseFloat(room.base_price).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500" x-text="'Estimativa para ' + nights + (nights === 1 ? ' diária' : ' diárias')"></p>
                        <p class="mt-1 text-2xl font-bold text-slate-950" x-text="'R$ ' + parseFloat(room.estimate).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
                    </div>

                    <button type="button"
                            class="mt-5 inline-flex items-center justify-center rounded-lg border py-2.5 text-sm font-semibold transition"
                            :class="selectedRoom && selectedRoom.id === room.id 
                                ? 'bg-sky-600 border-sky-600 text-white hover:bg-sky-700' 
                                : 'bg-sky-50 border-sky-200 text-sky-700 hover:bg-sky-600 hover:text-white'">
                        <span x-text="selectedRoom && selectedRoom.id === room.id ? 'Selecionado ✓' : 'Selecionar quarto'"></span>
                    </button>
                </div>
            </article>
        </template>

        <template x-if="rooms.length === 0">
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <h4 class="font-semibold text-slate-900">Nenhum quarto disponível</h4>
                <p class="mt-1 text-sm text-slate-500">Tente ajustar o período ou a quantidade de hóspedes.</p>
            </div>
        </template>
    </div>

    <!-- Navegação do Passo -->
    <div class="mt-8 flex justify-between border-t border-slate-100 pt-5">
        <button type="button" @click="step = 1"
                class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 shadow-sm transition">
            Voltar
        </button>
        <button type="button" @click="step = 3" :disabled="!selectedRoom"
                class="rounded-lg bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 disabled:opacity-50">
            Avançar
        </button>
    </div>
</div>
