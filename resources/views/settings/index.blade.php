<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Configurações da Pousada" subtitle="Gerencie as informações e preferências de operação">
        </x-page-header>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <x-flash-message />

            {{-- Abas de Navegação --}}
            <div class="flex gap-1 bg-slate-100 p-1 rounded-xl w-fit">
                <a href="#hotel-info" onclick="showTab('hotel')" id="tab-hotel"
                   class="px-4 py-2 rounded-lg text-sm font-semibold transition tab-btn tab-active">
                    Dados da Pousada
                </a>
                <a href="#operation" onclick="showTab('operation')" id="tab-operation"
                   class="px-4 py-2 rounded-lg text-sm font-semibold transition tab-btn">
                    Operação
                </a>
            </div>

            {{-- Painel: Dados da Pousada --}}
            <div id="panel-hotel">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Informações do Estabelecimento</h2>

                    <form action="{{ route('settings.hotel') }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Nome da Pousada *</label>
                                <input type="text" name="name" value="{{ old('name', $hotel->name) }}" required
                                       class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Razão Social</label>
                                <input type="text" name="legal_name" value="{{ old('legal_name', $hotel->legal_name) }}"
                                       class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">CNPJ / CPF</label>
                                <input type="text" name="document_number" value="{{ old('document_number', $hotel->document_number) }}"
                                       placeholder="00.000.000/0001-00"
                                       class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Telefone</label>
                                <input type="text" name="phone" value="{{ old('phone', $hotel->phone) }}"
                                       placeholder="(XX) XXXXX-XXXX"
                                       class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">E-mail</label>
                            <input type="email" name="email" value="{{ old('email', $hotel->email) }}"
                                   class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Endereço</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Logradouro</label>
                                    <input type="text" name="address_line" value="{{ old('address_line', $hotel->address_line) }}"
                                           placeholder="Rua, número, complemento"
                                           class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">CEP</label>
                                        <input type="text" name="postal_code" value="{{ old('postal_code', $hotel->postal_code) }}"
                                               placeholder="00000-000"
                                               class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Cidade</label>
                                        <input type="text" name="city" value="{{ old('city', $hotel->city) }}"
                                               class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Estado</label>
                                        <input type="text" name="state" value="{{ old('state', $hotel->state) }}"
                                               placeholder="SC"
                                               class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Salvar Dados
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Painel: Operação --}}
            <div id="panel-operation" class="hidden">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Configurações de Operação</h2>

                    <form action="{{ route('settings.operation') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        {{-- Horários de Check-in / Check-out --}}
                        <div>
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Horários Padrão</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Horário de Check-in</label>
                                    <input type="time" name="checkin_time"
                                           value="{{ old('checkin_time', $settings ? substr($settings->checkin_time, 0, 5) : '14:00') }}"
                                           class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Horário de Check-out</label>
                                    <input type="time" name="checkout_time"
                                           value="{{ old('checkout_time', $settings ? substr($settings->checkout_time, 0, 5) : '12:00') }}"
                                           class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                                </div>
                            </div>
                        </div>

                        {{-- Política de Cancelamento --}}
                        <div class="pt-4 border-t border-slate-100">
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Política de Cancelamento</h3>
                            <p class="text-xs text-slate-400 mb-3">Este texto pode ser exibido no comprovante de reserva e na fatura.</p>
                            <textarea name="cancellation_policy" rows="4"
                                      placeholder="Ex: Cancelamentos realizados com até 48h de antecedência não serão cobrados..."
                                      class="w-full rounded-xl border border-slate-200 text-sm py-2.5 px-3.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">{{ old('cancellation_policy', $settings?->cancellation_policy) }}</textarea>
                        </div>

                        {{-- Opções avançadas --}}
                        <div class="pt-4 border-t border-slate-100 space-y-4">
                            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Comportamento do Sistema</h3>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="hidden" name="auto_confirm_reservations" value="0">
                                <input type="checkbox" name="auto_confirm_reservations" value="1"
                                       {{ ($settings?->auto_confirm_reservations) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700">Confirmar reservas automaticamente ao criar</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="hidden" name="overbooking_allowed" value="0">
                                <input type="checkbox" name="overbooking_allowed" value="1"
                                       {{ ($settings?->overbooking_allowed) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-slate-700">Permitir overbooking (reservar mesmo sem disponibilidade)</span>
                            </label>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        function showTab(tab) {
            // Panels
            document.getElementById('panel-hotel').classList.add('hidden');
            document.getElementById('panel-operation').classList.add('hidden');
            document.getElementById('panel-' + tab).classList.remove('hidden');

            // Buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('tab-active', 'bg-white', 'shadow-sm', 'text-indigo-700');
                btn.classList.add('text-slate-500');
            });
            const active = document.getElementById('tab-' + tab);
            active.classList.add('bg-white', 'shadow-sm', 'text-indigo-700');
            active.classList.remove('text-slate-500');
        }

        // Init active state styling
        document.addEventListener('DOMContentLoaded', () => {
            showTab('hotel');
        });
    </script>

    <style>
        .tab-active {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            color: #4338ca;
        }
    </style>
</x-app-layout>
