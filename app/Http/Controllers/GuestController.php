<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\UpdateGuestRequest;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Guest::query();

        // Se houver RLS ativo na query (configurado no schema/middleware), 
        // ele vai isolar automaticamente, caso contrário o Model boot filter
        // ou essa query precisaria de: $query->where('tenant_id', current_tenant());
        // Como o BD usa RLS no IAM e nas outras tabelas, mas CRM Guests não teve a policy 
        // explícita no v4, vamos ser seguros e adicionar aqui caso a policy falhe:
        if ($tenantId = auth()->user()?->tenant_id) {
            $query->where('tenant_id', $tenantId);
        }

        // Busca
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('document_number', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        $guests = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('guests.index', compact('guests', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('guests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGuestRequest $request)
    {
        $data = $request->validated();
        
        // tenant_id já é preenchido pelo Guest::booted(), 
        // mas podemos forçar para garantir
        $data['tenant_id'] = auth()->user()?->tenant_id;
        
        Guest::create($data);

        return redirect()->route('guests.index')->with('success', 'Hóspede cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guest $guest)
    {
        // Verifica tenant (segurança extra se RLS falhar)
        if ($guest->tenant_id !== auth()->user()?->tenant_id) {
            abort(403);
        }
        
        return view('guests.edit', compact('guest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGuestRequest $request, Guest $guest)
    {
        if ($guest->tenant_id !== auth()->user()?->tenant_id) {
            abort(403);
        }

        $guest->update($request->validated());

        return redirect()->route('guests.index')->with('success', 'Hóspede atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guest $guest)
    {
        if ($guest->tenant_id !== auth()->user()?->tenant_id) {
            abort(403);
        }

        $guest->delete();

        return redirect()->route('guests.index')->with('success', 'Hóspede removido com sucesso!');
    }
}
