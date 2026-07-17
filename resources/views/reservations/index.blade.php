<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Reservas') }}
            </h2>
            <a href="{{ route('reservations.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                Nova Reserva
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form method="GET" action="{{ route('reservations.index') }}" class="mb-6 flex">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Buscar por código localizador ou nome do hóspede..." class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block w-full mt-1">
                        <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 mt-1 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ease-in-out duration-150">
                            Buscar
                        </button>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localizador</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hóspede</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reserva</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estadia</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Total</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'confirmed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'canceled' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        'no_show' => 'bg-slate-100 text-slate-800 border-slate-200',
                                    ];
                                    $stayStatusColors = [
                                        'awaiting_checkin' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'checked_in' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                        'checked_out' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pendente',
                                        'confirmed' => 'Confirmada',
                                        'canceled' => 'Cancelada',
                                        'no_show' => 'No-show',
                                        'refunded' => 'Reembolsada',
                                    ];
                                    $stayStatusLabels = [
                                        'awaiting_checkin' => 'Ag. Check-in',
                                        'checked_in' => 'Hospedado',
                                        'checked_out' => 'Finalizado',
                                    ];
                                @endphp
                                @forelse ($reservations as $res)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            #{{ $res->locator_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $res->mainGuest->full_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $res->check_in_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $res->check_out_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold border {{ $statusColors[$res->reservation_status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                                {{ $statusLabels[$res->reservation_status] ?? $res->reservation_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold border {{ $stayStatusColors[$res->stay_status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                                {{ $stayStatusLabels[$res->stay_status] ?? $res->stay_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                            R$ {{ number_format($res->total_amount, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('reservations.show', $res) }}" class="text-indigo-600 hover:text-indigo-900">Gerenciar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhuma reserva encontrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $reservations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
