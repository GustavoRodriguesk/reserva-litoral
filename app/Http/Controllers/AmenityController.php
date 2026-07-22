<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmenityController extends Controller
{
    /**
     * Display a listing of amenities sorted by name.
     */
    public function index(): View
    {
        $amenities = Amenity::orderBy('name')->get();

        return view('amenities.index', compact('amenities'));
    }

    /**
     * Store a newly created amenity in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:pgsql.core.amenities,name',
            'icon' => 'nullable|string|max:60',
        ]);

        Amenity::create($validated);

        return redirect()->back()->with('success', 'Comodidade criada com sucesso.');
    }

    /**
     * Remove the specified amenity from storage.
     */
    public function destroy(Amenity $amenity): RedirectResponse
    {
        $amenity->delete();

        return redirect()->back()->with('success', 'Comodidade removida com sucesso.');
    }
}
