<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Categorias de Quartos" subtitle="Gerencie os tipos de acomodações, preços de base, comodidades e fotos.">
            <x-slot name="actions">
                <a href="{{ route('room-types.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nova Categoria
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
            @endif

            @if($roomTypes->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <x-empty-state title="Nenhuma categoria de quarto cadastrada" description="Categorias definem tipos de acomodação física como Standard, Suíte Luxo, Quarto Família, etc.">
                        <x-slot name="action">
                            <a href="{{ route('room-types.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                                Adicionar Primeira Categoria
                            </a>
                        </x-slot>
                    </x-empty-state>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($roomTypes as $type)
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between group">
                            <div>
                                {{-- Banner / Imagem Principal --}}
                                <div class="h-48 bg-slate-100 relative overflow-hidden">
                                    @if($type->images->isNotEmpty())
                                        <img src="{{ $type->images->first()->file->url }}" alt="{{ $type->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 stroke-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                            <span class="text-xs">Sem fotos cadastradas</span>
                                        </div>
                                    @endif
                                    <div class="absolute top-4 right-4">
                                        <x-status-badge :status="$type->is_active ? 'active' : 'inactive'" />
                                    </div>
                                </div>

                                {{-- Conteúdo --}}
                                <div class="p-6 space-y-4">
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight group-hover:text-indigo-600 transition">{{ $type->name }}</h3>
                                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-semibold">
                                            Capacidade: {{ $type->base_capacity }} a {{ $type->max_capacity }} hóspedes
                                        </p>
                                    </div>

                                    <p class="text-slate-500 text-sm line-clamp-3 leading-relaxed">
                                        {{ $type->description ?? 'Nenhuma descrição informada.' }}
                                    </p>

                                    {{-- Comodidades --}}
                                    @if($type->amenities->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5 pt-2">
                                            @foreach($type->amenities as $amenity)
                                                <span class="inline-flex px-2 py-0.5 rounded-lg bg-slate-50 border border-slate-100 text-slate-600 text-[10px] font-medium items-center gap-1">
                                                    {{ $amenity->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Footer / Preço e Ações --}}
                            <div class="p-6 pt-0 border-t border-slate-50 mt-auto">
                                <div class="flex items-baseline justify-between py-4">
                                    <span class="text-xs text-slate-400 font-medium">Preço base:</span>
                                    <span class="text-lg font-bold text-slate-800">R$ {{ number_format($type->base_price, 2, ',', '.') }}<span class="text-xs text-slate-400 font-normal">/noite</span></span>
                                </div>

                                <div class="flex gap-2">
                                    <a href="{{ route('room-types.edit', $type) }}" class="flex-1 inline-flex justify-center items-center gap-1 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold py-2.5 transition">
                                        Editar Categoria
                                    </a>
                                    <form action="{{ route('room-types.destroy', $type) }}" method="POST" class="inline" onsubmit="return confirm('Atenção: Excluir esta categoria irá desvincular todos os quartos a ela associados. Confirmar exclusão?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-rose-100 text-rose-600 hover:bg-rose-50 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
