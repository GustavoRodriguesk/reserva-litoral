<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Editar Categoria: ' . $roomType->name" subtitle="Configure fotos, descrições, comodidades e preços base da categoria." :backUrl="route('room-types.index')" />
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-flash-message />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Informações e Comodidades (2/3) --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="p-6 border-b border-slate-50">
                            <h3 class="font-bold text-slate-800">Modificar Detalhes da Categoria</h3>
                        </div>

                        <form action="{{ route('room-types.update', $roomType) }}" method="POST" class="p-6 space-y-6">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nome da Categoria --}}
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nome da Categoria</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $roomType->name) }}" required
                                           class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                </div>

                                {{-- Capacidade Base --}}
                                <div>
                                    <label for="base_capacity" class="block text-sm font-semibold text-slate-700 mb-1.5">Capacidade Base (Adultos)</label>
                                    <input type="number" name="base_capacity" id="base_capacity" value="{{ old('base_capacity', $roomType->base_capacity) }}" min="1" required
                                           class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                </div>

                                {{-- Capacidade Máxima --}}
                                <div>
                                    <label for="max_capacity" class="block text-sm font-semibold text-slate-700 mb-1.5">Capacidade Máxima (Total)</label>
                                    <input type="number" name="max_capacity" id="max_capacity" value="{{ old('max_capacity', $roomType->max_capacity) }}" min="1" required
                                           class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                </div>

                                {{-- Preço Base --}}
                                <div>
                                    <label for="base_price" class="block text-sm font-semibold text-slate-700 mb-1.5">Preço Base Diária (R$)</label>
                                    <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $roomType->base_price) }}" step="0.01" min="0" required
                                           class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                </div>

                                {{-- Status Ativo --}}
                                <div class="flex items-center gap-3 pt-6 pl-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $roomType->is_active) ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="is_active" class="text-sm font-semibold text-slate-700 cursor-pointer">Categoria Ativa no Sistema</label>
                                </div>

                                {{-- Descrição --}}
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">Descrição Comercial</label>
                                    <textarea name="description" id="description" rows="4" placeholder="Escreva os detalhes, diferenciais e informações sobre esta acomodação..."
                                              class="block w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">{{ old('description', $roomType->description) }}</textarea>
                                </div>

                                {{-- Comodidades (Checkboxes) --}}
                                <div class="md:col-span-2 space-y-3 pt-4 border-t border-slate-50">
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-800">Comodidades Disponíveis</h4>
                                        <p class="text-xs text-slate-400 mt-0.5">Selecione os diferenciais deste tipo de quarto</p>
                                    </div>

                                    @if($amenities->isEmpty())
                                        <p class="text-slate-400 text-xs py-2">
                                            Nenhuma comodidade global cadastrada. Cadastre em <a href="{{ route('amenities.index') }}" class="text-indigo-600 underline font-semibold">Comodidades</a> primeiro.
                                        </p>
                                    @else
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            @foreach($amenities as $am)
                                                <label class="flex items-center gap-2.5 p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition cursor-pointer">
                                                    <input type="checkbox" name="amenities[]" value="{{ $am->id }}"
                                                           {{ in_array($am->id, old('amenities', $roomType->amenities->pluck('id')->toArray())) ? 'checked' : '' }}
                                                           class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span class="text-xs font-semibold text-slate-700">{{ $am->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 border-t border-slate-50 pt-6">
                                <a href="{{ route('room-types.index') }}"
                                   class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                                    Voltar
                                </a>
                                <button type="submit"
                                        class="inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Galeria de Fotos (1/3) --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <div>
                            <h3 class="font-bold text-slate-800">Galeria de Fotos</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Faça upload de fotos para a galeria pública.</p>
                        </div>

                        {{-- Upload Form --}}
                        <form action="{{ route('room-types.images.upload', $roomType) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div class="border-2 border-dashed border-slate-200 hover:border-indigo-400 rounded-2xl p-6 text-center transition cursor-pointer relative group">
                                <input type="file" name="image" required onchange="this.form.submit()"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <div class="flex flex-col items-center justify-center gap-2 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 stroke-1.5 group-hover:text-indigo-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span class="text-xs font-semibold text-slate-600 group-hover:text-indigo-600 transition">Clique para adicionar foto</span>
                                    <span class="text-[10px] text-slate-400">PNG, JPG ou WEBP (máx. 5MB)</span>
                                </div>
                            </div>
                        </form>

                        {{-- Imagens Existentes --}}
                        @if($roomType->images->isEmpty())
                            <p class="text-slate-400 text-xs text-center py-6">Nenhuma imagem cadastrada para esta categoria.</p>
                        @else
                            <div class="grid grid-cols-2 gap-3" id="gallery-rack">
                                @foreach($roomType->images as $image)
                                    <div class="relative bg-slate-50 border border-slate-100 rounded-xl overflow-hidden group h-24">
                                        <img src="{{ $image->file->url }}" alt="" class="w-full h-full object-cover">
                                        
                                        {{-- Botão Excluir --}}
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                                            <form action="{{ route('room-types.images.delete', [$roomType, $image]) }}" method="POST" onsubmit="return confirm('Deseja deletar esta imagem da galeria?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white rounded-lg p-1.5 shadow-md transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
