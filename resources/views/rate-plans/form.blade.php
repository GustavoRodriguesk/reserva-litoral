@php
    $isEdit = isset($ratePlan);
    $title  = $isEdit ? 'Editar Tarifário' : 'Novo Tarifário';
    $action = $isEdit ? route('rate-plans.update', $ratePlan) : route('rate-plans.store');
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$title" subtitle="Configure as regras e condições do plano de tarifa"
                       :backUrl="route('rate-plans.index')">
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">

                <form action="{{ $action }}" method="POST" class="space-y-5">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    {{-- Tipo de Quarto --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tipo de Quarto *</label>
                        <select name="room_type_id" required
                                class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            <option value="">Selecione...</option>
                            @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}"
                                    {{ old('room_type_id', $isEdit ? $ratePlan->room_type_id : request('room_type_id')) == $rt->id ? 'selected' : '' }}>
                                    {{ $rt->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('room_type_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Nome --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nome do Tarifário *</label>
                            <input type="text" name="name" value="{{ old('name', $isEdit ? $ratePlan->name : '') }}"
                                   placeholder="Ex: Tarifa Flexível, Não-Reembolsável, Alta Temporada..."
                                   required
                                   class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Código --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Código Interno</label>
                            <input type="text" name="code" value="{{ old('code', $isEdit ? $ratePlan->code : '') }}"
                                   placeholder="Ex: BAR, NRF, CORP..."
                                   class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm font-mono">
                            <p class="text-[10px] text-slate-400 mt-1">Código curto para identificação e integração com canais.</p>
                        </div>

                        {{-- Regime de Alimentação --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Regime Alimentar *</label>
                            <select name="meal_plan" required
                                    class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                @php
                                    $mealOptions = [
                                        'room_only'    => 'Apenas Quarto (Room Only)',
                                        'breakfast'    => 'Café da Manhã (BB)',
                                        'half_board'   => 'Meia Pensão (HB)',
                                        'full_board'   => 'Pensão Completa (FB)',
                                        'all_inclusive'=> 'All Inclusive (AI)',
                                    ];
                                @endphp
                                @foreach($mealOptions as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('meal_plan', $isEdit ? $ratePlan->meal_plan : 'room_only') === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Antecedência Mínima --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Antecedência Mínima (dias)</label>
                        <input type="number" name="min_advance_days" min="0"
                               value="{{ old('min_advance_days', $isEdit ? $ratePlan->min_advance_days : 0) }}"
                               class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        <p class="text-[10px] text-slate-400 mt-1">0 = sem restrição de antecedência.</p>
                    </div>

                    {{-- Descrição --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Descrição</label>
                        <textarea name="description" rows="3"
                                  placeholder="Descreva o que está incluso neste tarifário..."
                                  class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">{{ old('description', $isEdit ? $ratePlan->description : '') }}</textarea>
                    </div>

                    {{-- Política de Cancelamento --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Política de Cancelamento</label>
                        <textarea name="cancellation_policy" rows="3"
                                  placeholder="Ex: Cancelamento gratuito até 48h antes. Após esse prazo, a primeira noite será cobrada..."
                                  class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">{{ old('cancellation_policy', $isEdit ? $ratePlan->cancellation_policy : '') }}</textarea>
                    </div>

                    {{-- Checkboxes --}}
                    <div class="pt-2 border-t border-slate-100 space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="is_refundable" value="0">
                            <input type="checkbox" name="is_refundable" value="1"
                                   {{ old('is_refundable', $isEdit ? $ratePlan->is_refundable : true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">Tarifa reembolsável</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="is_default" value="0">
                            <input type="checkbox" name="is_default" value="1"
                                   {{ old('is_default', $isEdit ? $ratePlan->is_default : false) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">Definir como tarifário padrão deste tipo de quarto</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $isEdit ? $ratePlan->is_active : true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">Tarifário ativo</span>
                        </label>
                    </div>

                    {{-- Ações --}}
                    <div class="flex items-center justify-between pt-4">
                        <a href="{{ route('rate-plans.index') }}"
                           class="text-sm font-semibold text-slate-500 hover:text-slate-700">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ $isEdit ? 'Salvar Alterações' : 'Criar Tarifário' }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
