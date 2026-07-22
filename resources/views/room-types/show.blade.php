<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('room-types.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">{{ $roomType->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Visualização da categoria no sistema</p>
                </div>
            </div>
            <a href="{{ route('room-types.edit', $roomType) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                Editar Categoria
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Galeria de Fotos e Descrição (2/3) --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Imagem Principal / Galeria --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden p-6 space-y-4">
                        @if($roomType->images->isNotEmpty())
                            <div class="aspect-video w-full rounded-xl overflow-hidden bg-slate-50 border border-slate-100">
                                <img src="{{ $roomType->images->first()->file->url }}" alt="{{ $roomType->name }}" class="w-full h-full object-cover">
                            </div>
                            @if($roomType->images->count() > 1)
                                <div class="grid grid-cols-4 gap-3">
                                    @foreach($roomType->images as $image)
                                        <div class="aspect-video rounded-lg overflow-hidden bg-slate-50 border border-slate-100 cursor-pointer hover:border-indigo-500 transition">
                                            <img src="{{ $image->file->url }}" alt="" class="w-full h-full object-cover">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="aspect-video w-full rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center text-slate-400 gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 stroke-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                <span class="text-sm font-medium">Nenhuma foto adicionada</span>
                            </div>
                        @endif
                    </div>

                    {{-- Descrição --}}
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <h3 class="font-bold text-slate-800">Descrição Comercial</h3>
                        <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-line">
                            {{ $roomType->description ?? 'Nenhuma descrição adicionada para esta categoria.' }}
                        </p>
                    </div>
                </div>

                {{-- Informações Operacionais e Detalhes --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</span>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="w-2.5 h-2.5 rounded-full {{ $roomType->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                <span class="text-sm font-bold text-slate-800">{{ $roomType->is_active ? 'Ativa no sistema' : 'Inativa/Oculta' }}</span>
                            </div>
                        </div>

                        <div class="border-t border-slate-50 pt-4">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Preço de Base Comercial</span>
                            <p class="text-2xl font-black text-slate-800 mt-1">R$ {{ number_format($roomType->base_price, 2, ',', '.') }}<span class="text-xs font-normal text-slate-400">/noite</span></p>
                        </div>

                        <div class="border-t border-slate-50 pt-4 grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Capacidade Base</span>
                                <p class="text-sm font-bold text-slate-800 mt-1">{{ $roomType->base_capacity }} Adultos</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Capacidade Máx</span>
                                <p class="text-sm font-bold text-slate-800 mt-1">{{ $roomType->max_capacity }} Pessoas</p>
                            </div>
                        </div>

                        {{-- Comodidades --}}
                        @if($roomType->amenities->isNotEmpty())
                            <div class="border-t border-slate-50 pt-4 space-y-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Comodidades</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($roomType->amenities as $amenity)
                                        <span class="inline-flex px-2.5 py-1 rounded-xl bg-slate-50 border border-slate-100 text-slate-700 text-xs font-semibold">
                                            {{ $amenity->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
