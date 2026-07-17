<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-8">

        <div class="grid grid-cols-4 gap-6">

            <div class="bg-white shadow rounded-lg p-6">
                <h3>Receita Hoje</h3>
                <p>R$ {{ number_format($revenueToday,2,',','.') }}</p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3>Ocupação</h3>
                <p>{{ $occupancy }}%</p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3>Check-ins</h3>
                <p>{{ $checkins }}</p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3>Check-outs</h3>
                <p>{{ $checkouts }}</p>
            </div>

        </div>

    </div>

</x-app-layout>