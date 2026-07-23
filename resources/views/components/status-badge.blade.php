@props([
    'status',
    'label' => null,
])

@php
    $configs = [
        // Stay status
        'awaiting_checkin' => ['class' => 'bg-blue-50 text-blue-700 border-blue-100', 'label' => 'Aguardando Check-in'],
        'checked_in'       => ['class' => 'bg-indigo-50 text-indigo-700 border-indigo-100', 'label' => 'Hospedado'],
        'checked_out'      => ['class' => 'bg-purple-50 text-purple-700 border-purple-100', 'label' => 'Finalizada'],

        // Reservation status
        'confirmed'        => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'label' => 'Confirmada'],
        'pending'          => ['class' => 'bg-amber-50 text-amber-700 border-amber-100', 'label' => 'Pendente'],
        'canceled'         => ['class' => 'bg-rose-50 text-rose-700 border-rose-100', 'label' => 'Cancelada'],

        // Room status
        'available'        => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'label' => 'Disponível'],
        'occupied'         => ['class' => 'bg-indigo-50 text-indigo-700 border-indigo-100', 'label' => 'Ocupado'],
        'cleaning'         => ['class' => 'bg-amber-50 text-amber-700 border-amber-100', 'label' => 'Sujo / Limpeza'],
        'maintenance'      => ['class' => 'bg-orange-50 text-orange-700 border-orange-100', 'label' => 'Manutenção'],
        'blocked'          => ['class' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'Bloqueado'],

        // Booleans / Active
        'active'           => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'label' => 'Ativo'],
        'inactive'         => ['class' => 'bg-slate-100 text-slate-600 border-slate-200', 'label' => 'Inativo'],
        '1'                => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'label' => 'Ativo'],
        '0'                => ['class' => 'bg-slate-100 text-slate-600 border-slate-200', 'label' => 'Inativo'],
        'true'             => ['class' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'label' => 'Ativo'],
        'false'            => ['class' => 'bg-slate-100 text-slate-600 border-slate-200', 'label' => 'Inativo'],
    ];

    $stKey = strtolower((string)$status);
    $cfg = $configs[$stKey] ?? ['class' => 'bg-slate-50 text-slate-600 border-slate-200', 'label' => ucfirst($stKey)];
    $displayLabel = $label ?? $cfg['label'];
    $badgeClass = $cfg['class'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 text-[10px] font-bold border rounded-full uppercase tracking-wider {$badgeClass}"]) }}>
    {{ $displayLabel }}
</span>
