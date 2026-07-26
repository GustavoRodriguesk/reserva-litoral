<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Mapa de Reservas" subtitle="Planejamento">
            <x-slot name="actions">
                <!-- Legenda -->
                <div class="flex flex-wrap gap-4 text-xs">
                    <div class="flex items-center gap-1.5 text-slate-600">
                        <span class="w-3 h-3 rounded bg-sky-100 border border-sky-200 block"></span>
                        <span>Confirmada (Ag. Check-in)</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-indigo-700">
                        <span class="w-3 h-3 rounded bg-indigo-600 border border-indigo-700 block"></span>
                        <span>Hospedado (Check-in Ativo)</span>
                    </div>
                </div>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div x-data="planningBoard()" class="py-8 sm:py-10">
        <div class="mx-auto max-w-8xl px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filtros e Navegação -->
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Navegação de Semana -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('planning.index', ['start' => $startDate->copy()->subWeek()->toDateString(), 'room_type_id' => $selectedRoomTypeId]) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                        ← Ant.
                    </a>
                    <a href="{{ route('planning.index', ['start' => now()->toDateString(), 'room_type_id' => $selectedRoomTypeId]) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                        Hoje
                    </a>
                    <a href="{{ route('planning.index', ['start' => $startDate->copy()->addWeek()->toDateString(), 'room_type_id' => $selectedRoomTypeId]) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                        Próx. →
                    </a>
                    <span class="text-sm font-bold text-slate-800 ml-2">
                        {{ $startDate->translatedFormat('d \d\e F') }} a {{ $endDate->translatedFormat('d \d\e F \d\e Y') }}
                    </span>
                </div>

                <!-- Formulário de Filtros -->
                <form method="GET" action="{{ route('planning.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Datepicker -->
                    <div>
                        <input type="date" name="start" value="{{ $startDate->toDateString() }}"
                               class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                    </div>

                    <!-- Tipo de Acomodação -->
                    <div>
                        <select name="room_type_id" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500">
                            <option value="">Todos os tipos</option>
                            @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}" {{ $selectedRoomTypeId === $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold px-4 py-2 shadow-sm transition">
                        Filtrar
                    </button>
                </form>
            </div>

            <!-- Grid de Planejamento -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse select-none">
                        
                        <!-- Cabeçalho de Datas -->
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-r border-slate-200 bg-slate-50 sticky left-0 z-20 w-44">
                                    Quarto
                                </th>
                                @foreach($planning['dates'] as $date)
                                    <th class="px-2 py-3 text-center border-r border-slate-200 min-w-[120px] {{ $date->isToday() ? 'bg-sky-50/50' : '' }}">
                                        <p class="text-xs font-semibold uppercase tracking-wider {{ $date->isToday() ? 'text-sky-700' : 'text-slate-400' }}">
                                            {{ $date->translatedFormat('D') }}
                                        </p>
                                        <p class="text-sm font-bold mt-0.5 {{ $date->isToday() ? 'text-sky-700' : 'text-slate-800' }}">
                                            {{ $date->format('d/m') }}
                                        </p>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <!-- Corpo de Quartos e Reservas -->
                        <tbody class="divide-y divide-slate-100">
                            @foreach($planning['rooms'] as $roomData)
                                @php
                                    $room = $roomData['room'];
                                    $roomReservations = $roomData['reservations'];
                                    $skipDays = 0;
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    
                                    <!-- Identificador do Quarto (Sticky left) -->
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-slate-900 border-r border-slate-200 bg-slate-50/90 sticky left-0 z-10 w-44">
                                        <div>Quarto {{ $room->number }}</div>
                                        <div class="text-xs text-slate-500 font-normal truncate max-w-[150px]">{{ $room->roomType->name }}</div>
                                    </td>

                                    <!-- Células de Calendário -->
                                    @php
                                        $dates = $planning['dates'];
                                        $totalDays = count($dates);
                                        $di = 0; // date index
                                    @endphp
                                    @while($di < $totalDays)
                                        @php
                                            $date = $dates[$di];
                                            $dateStr = $date->toDateString();

                                            // Encontra reserva que cobre a data
                                            $matchingRes = $roomReservations->first(function($res) use ($dateStr) {
                                                return $dateStr >= $res['check_in_date'] && $dateStr < $res['check_out_date'];
                                            });
                                        @endphp

                                        @if($matchingRes)
                                            @php
                                                // Calcula quantas células este bloco ocupa (até o fim das datas visíveis)
                                                $span = 0;
                                                $tempDi = $di;
                                                while ($tempDi < $totalDays) {
                                                    $tempDate = $dates[$tempDi]->toDateString();
                                                    if ($tempDate >= $matchingRes['check_in_date'] && $tempDate < $matchingRes['check_out_date']) {
                                                        $span++;
                                                        $tempDi++;
                                                    } else {
                                                        break;
                                                    }
                                                }
                                                $di = $tempDi; // avança o índice para além do bloco
                                            @endphp
                                            <td colspan="{{ $span }}" class="p-1 align-middle border-r border-slate-200 z-0">
                                                <div draggable="true"
                                                     @dragstart="dragStart($event, {{ json_encode($matchingRes) }})"
                                                     @dragend="dragEnd($event)"
                                                     class="rounded-xl p-3 text-xs shadow-sm cursor-grab transition border select-none
                                                            {{ $matchingRes['stay_status'] === 'checked_in'
                                                                ? 'bg-indigo-600 border-indigo-700 text-white hover:bg-indigo-700'
                                                                : 'bg-sky-50 border-sky-200 text-sky-800 hover:bg-sky-100' }}"
                                                     @click="window.location.href = '/reservations/{{ $matchingRes['id'] }}'">

                                                    <div class="flex justify-between items-center font-bold gap-1">
                                                        <span class="truncate">{{ $matchingRes['guest_name'] }}</span>
                                                        <span class="opacity-70 shrink-0">#{{ $matchingRes['locator_code'] }}</span>
                                                    </div>

                                                    <div class="mt-1 opacity-90 flex justify-between items-center">
                                                        <span>
                                                            {{ \Carbon\Carbon::parse($matchingRes['check_in_date'])->format('d/m') }}
                                                            → {{ \Carbon\Carbon::parse($matchingRes['check_out_date'])->format('d/m') }}
                                                        </span>
                                                        <span class="text-[10px] font-semibold bg-white/20 px-1.5 py-0.5 rounded">
                                                            {{ $matchingRes['stay_status'] === 'checked_in' ? 'Hospedado' : 'Confirmada' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        @else
                                            @php $di++; @endphp
                                            <td class="p-1 align-middle border-r border-slate-200 h-[72px] transition-colors duration-150"
                                                @dragover.prevent="dragOver($event, '{{ $room->id }}', '{{ $dateStr }}')"
                                                @dragleave="dragLeave($event)"
                                                @drop="drop($event, '{{ $room->id }}', '{{ $dateStr }}')"
                                                :class="activeDropRoom === '{{ $room->id }}' && activeDropDate === '{{ $dateStr }}' ? 'bg-sky-100 border-sky-400' : ''">
                                                <div class="w-full h-full min-h-[50px] rounded-lg border border-dashed border-transparent hover:border-slate-300"></div>
                                            </td>
                                        @endif
                                    @endwhile
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>

        </div>

        <!-- Overlay de Salvando / Processamento -->
        <div x-show="loading" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center" style="display: none;">
            <div class="bg-white rounded-2xl p-6 shadow-xl flex items-center gap-4">
                <svg class="h-6 w-6 animate-spin text-sky-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="font-bold text-slate-800 text-sm">Remanejando reserva...</span>
            </div>
        </div>
    </div>

    <!-- Script Alpine.js para o Painel de Planejamento -->
    <script>
        function planningBoard() {
            return {
                loading: false,
                draggedReservation: null,
                activeDropRoom: null,
                activeDropDate: null,

                dragStart(event, reservation) {
                    this.draggedReservation = reservation;
                    // Define o efeito visual de movimentação
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', reservation.id);
                },

                dragEnd(event) {
                    this.draggedReservation = null;
                    this.activeDropRoom = null;
                    this.activeDropDate = null;
                },

                dragOver(event, roomId, dateStr) {
                    this.activeDropRoom = roomId;
                    this.activeDropDate = dateStr;
                },

                dragLeave(event) {
                    // Limpa estados apenas se sair do drop zone correspondente
                },

                getDurationInNights(checkIn, checkOut) {
                    const start = new Date(checkIn + 'T12:00:00');
                    const end = new Date(checkOut + 'T12:00:00');
                    const diffTime = Math.abs(end - start);
                    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                },

                addDays(dateStr, days) {
                    const date = new Date(dateStr + 'T12:00:00');
                    date.setDate(date.getDate() + days);
                    
                    const yyyy = date.getFullYear();
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const dd = String(date.getDate()).padStart(2, '0');
                    
                    return `${yyyy}-${mm}-${dd}`;
                },

                drop(event, roomId, dateStr) {
                    if (!this.draggedReservation) return;

                    const nights = this.getDurationInNights(
                        this.draggedReservation.check_in_date,
                        this.draggedReservation.check_out_date
                    );
                    const newCheckOut = this.addDays(dateStr, nights);

                    const confirmationMessage = `Deseja transferir a reserva do hóspede ${this.draggedReservation.guest_name} para o Quarto com ID ${roomId} no período de ${dateStr} a ${newCheckOut}?`;

                    if (confirm(confirmationMessage)) {
                        this.loading = true;
                        
                        const body = {
                            reservation_room_id: this.draggedReservation.reservation_room_id,
                            new_room_id: roomId,
                            new_check_in: dateStr,
                            new_check_out: newCheckOut
                        };

                        fetch('{{ route('planning.reallocate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(body)
                        })
                        .then(async res => {
                            const data = await res.json();
                            if (res.ok && data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message || 'Erro ao remanejar reserva.');
                                this.loading = false;
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Ocorreu um erro ao processar a requisição.');
                            this.loading = false;
                        });
                    }
                }
            };
        }
    </script>
</x-app-layout>
