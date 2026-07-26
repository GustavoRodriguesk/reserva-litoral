<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tarifários" subtitle="Gerencie os planos de tarifa por tipo de quarto">
            <x-slot name="actions">
                <a href="{{ route('rate-plans.create') }}"
                   class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Novo Tarifário
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-message />

            @forelse($roomTypes as $roomType)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                    {{-- Header do tipo de quarto --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50 rounded-t-2xl">
                        <div>
                            <h3 class="font-bold text-slate-800">{{ $roomType->name }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $roomType->ratePlans->count() }} {{ $roomType->ratePlans->count() == 1 ? 'tarifário' : 'tarifários' }}</p>
                        </div>
                        <a href="{{ route('rate-plans.create') }}?room_type_id={{ $roomType->id }}"
                           class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                            + Adicionar tarifa
                        </a>
                    </div>

                    @if($roomType->ratePlans->isEmpty())
                        <div class="py-8 text-center text-slate-400 text-sm italic">
                            Nenhum tarifário cadastrado para este tipo de quarto.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-xs text-slate-500 uppercase tracking-wider bg-white border-b border-slate-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold">Nome</th>
                                        <th class="px-6 py-3 text-left font-semibold">Código</th>
                                        <th class="px-6 py-3 text-left font-semibold">Regime</th>
                                        <th class="px-6 py-3 text-center font-semibold">Reembolsável</th>
                                        <th class="px-6 py-3 text-center font-semibold">Antecedência</th>
                                        <th class="px-6 py-3 text-center font-semibold">Status</th>
                                        <th class="px-6 py-3 text-right font-semibold">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($roomType->ratePlans as $plan)
                                        @php
                                            $mealLabels = [
                                                'room_only'    => 'Apenas Quarto',
                                                'breakfast'    => 'Café da Manhã',
                                                'half_board'   => 'Meia Pensão',
                                                'full_board'   => 'Pensão Completa',
                                                'all_inclusive'=> 'All Inclusive',
                                            ];
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-6 py-3">
                                                <span class="font-semibold text-slate-800">{{ $plan->name }}</span>
                                                @if($plan->is_default)
                                                    <span class="ml-2 text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full uppercase">Padrão</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $plan->code ?? '—' }}</td>
                                            <td class="px-6 py-3 text-slate-600">{{ $mealLabels[$plan->meal_plan] ?? $plan->meal_plan }}</td>
                                            <td class="px-6 py-3 text-center">
                                                @if($plan->is_refundable)
                                                    <span class="text-emerald-600 font-semibold text-xs">✓ Sim</span>
                                                @else
                                                    <span class="text-rose-500 font-semibold text-xs">✗ Não</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-3 text-center text-slate-600 text-xs">
                                                {{ $plan->min_advance_days == 0 ? 'Qualquer' : $plan->min_advance_days . ' dias' }}
                                            </td>
                                            <td class="px-6 py-3 text-center">
                                                @if($plan->is_active)
                                                    <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full uppercase">Ativo</span>
                                                @else
                                                    <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full uppercase">Inativo</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-3 text-right">
                                                <div class="flex justify-end gap-3">
                                                    <a href="{{ route('rate-plans.edit', $plan) }}"
                                                       class="text-indigo-600 hover:text-indigo-800 font-semibold text-xs">Editar</a>
                                                    <form action="{{ route('rate-plans.destroy', $plan) }}" method="POST" class="inline-block"
                                                          onsubmit="return confirm('Remover este tarifário?')">
                                                        @csrf @method('DELETE')
                                                        <button class="text-rose-500 hover:text-rose-700 font-semibold text-xs">Remover</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @empty
                <x-empty-state
                    title="Nenhum tipo de quarto cadastrado"
                    description="Cadastre um tipo de quarto primeiro para criar tarifários."
                >
                    <a href="{{ route('room-types.create') }}"
                       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition">
                        Criar Tipo de Quarto
                    </a>
                </x-empty-state>
            @endforelse

        </div>
    </div>
</x-app-layout>
