<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('rooms.show', $room) }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">Editar Quarto {{ $room->number }}</h2>
                <p class="text-sm text-gray-500 mt-1">Atualize os detalhes do quarto</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-50">
                    <h3 class="font-bold text-slate-800">Modificar Informações</h3>
                </div>

                <form action="{{ route('rooms.update', $room) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')
                    
                    {{-- Hotel ID (Tenant) --}}
                    <input type="hidden" name="hotel_id" value="{{ $room->hotel_id }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Número do Quarto --}}
                        <div>
                            <label for="number" class="block text-sm font-semibold text-slate-700 mb-1.5">Número do Quarto</label>
                            <input type="text" name="number" id="number" value="{{ old('number', $room->number) }}" required
                                   placeholder="Ex: 101, 102A..."
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('number') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('number')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Andar --}}
                        <div>
                            <label for="floor" class="block text-sm font-semibold text-slate-700 mb-1.5">Andar / Pavimento</label>
                            <input type="number" name="floor" id="floor" value="{{ old('floor', $room->floor) }}" min="0"
                                   placeholder="Ex: 1 para 1º andar..."
                                   class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('floor') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                            @error('floor')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tipo de Quarto --}}
                        <div class="md:col-span-2">
                            <label for="room_type_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Quarto</label>
                            <select name="room_type_id" id="room_type_id" required
                                    class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm @error('room_type_id') border-rose-300 focus:ring-rose-500 focus:border-rose-500 @enderror">
                                <option value="">Selecione um tipo...</option>
                                @foreach($roomTypes as $rt)
                                    <option value="{{ $rt->id }}" {{ old('room_type_id', $room->room_type_id) == $rt->id ? 'selected' : '' }}>
                                        {{ $rt->name }} - R$ {{ number_format($rt->base_price, 2, ',', '.') }}/noite (Capacidade: {{ $rt->max_capacity }} pessoas)
                                    </option>
                                @endforeach
                            </select>
                            @error('room_type_id')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>



                        {{-- Ativo --}}
                        <div class="md:col-span-2 flex items-start gap-3 rounded-xl border border-slate-100 p-4">
                            <input type="hidden" name="is_active" value="0">
                            <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $room->is_active) ? 'checked' : '' }}
                                   class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <label for="is_active" class="text-sm font-semibold text-slate-700 cursor-pointer">Quarto Ativo e Disponível no Sistema</label>
                                <p class="text-xs text-slate-500 mt-0.5">Se desativado, o quarto não constará na busca de disponibilidade de novas reservas.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-50 pt-6">
                        {{-- Botão Excluir --}}
                        <button type="button" onclick="confirmDelete()"
                                class="inline-flex justify-center items-center gap-1.5 rounded-xl border border-rose-200 text-rose-700 hover:bg-rose-50 px-4 py-2.5 text-sm font-semibold transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                            Excluir Quarto
                        </button>

                        <div class="flex gap-3">
                            <a href="{{ route('rooms.show', $room) }}"
                               class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                                Cancelar
                            </a>
                            <button type="submit"
                                    class="inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Form invisível de exclusão --}}
    <form id="delete-form" action="{{ route('rooms.destroy', $room) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    @push('scripts')
    <script>
        function confirmDelete() {
            if (confirm('Atenção: Excluir este quarto é permanente e removerá seu vínculo no histórico. Confirmar exclusão?')) {
                document.getElementById('delete-form').submit();
            }
        }
    </script>
    @endpush
</x-app-layout>
