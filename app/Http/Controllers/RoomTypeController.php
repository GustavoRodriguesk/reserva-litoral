<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\File;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    /**
     * Display a listing of room types for the authenticated user's hotel.
     */
    public function index(): View
    {
        $roomTypes = RoomType::where('hotel_id', Auth::user()->hotel_id)
            ->with(['images.file', 'amenities'])
            ->orderBy('name')
            ->get();

        return view('room-types.index', compact('roomTypes'));
    }

    /**
     * Show the form for creating a new room type.
     */
    public function create(): View
    {
        return view('room-types.create');
    }

    /**
     * Store a newly created room type in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'base_capacity' => 'required|integer|min:1',
            'max_capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
        ]);

        $validated['hotel_id'] = Auth::user()->hotel_id;

        $roomType = RoomType::create($validated);

        return redirect()
            ->route('room-types.edit', $roomType)
            ->with('success', 'Tipo de acomodação criado com sucesso.');
    }

    /**
     * Show the form for editing the specified room type.
     */
    public function edit(RoomType $roomType): View
    {
        $roomType->load(['images.file', 'amenities']);
        $amenities = Amenity::orderBy('name')->get();

        return view('room-types.edit', compact('roomType', 'amenities'));
    }

    /**
     * Update the specified room type in storage.
     */
    public function update(Request $request, RoomType $roomType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'base_capacity' => 'required|integer|min:1',
            'max_capacity' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:pgsql.core.amenities,id',
        ]);

        $roomType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_capacity' => $validated['base_capacity'],
            'max_capacity' => $validated['max_capacity'],
            'base_price' => $validated['base_price'],
        ]);

        $roomType->amenities()->sync($validated['amenities'] ?? []);

        return redirect()
            ->route('room-types.index')
            ->with('success', 'Tipo de acomodação atualizado com sucesso.');
    }

    /**
     * Remove the specified room type from storage.
     */
    public function destroy(RoomType $roomType): RedirectResponse
    {
        $roomType->delete();

        return redirect()
            ->route('room-types.index')
            ->with('success', 'Tipo de acomodação removido com sucesso.');
    }

    /**
     * Upload an image for the specified room type.
     */
    public function uploadImage(Request $request, RoomType $roomType): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $path = $request->file('image')->store("room-types/{$roomType->id}", 'public');

        DB::transaction(function () use ($request, $roomType, $path) {
            $file = File::create([
                'tenant_id' => Auth::user()->tenant_id,
                'original_name' => $request->file('image')->getClientOriginalName(),
                'storage_path' => $path,
                'mime_type' => $request->file('image')->getMimeType(),
                'size_bytes' => $request->file('image')->getSize(),
                'uploaded_by' => Auth::id(),
            ]);

            RoomImage::create([
                'room_type_id' => $roomType->id,
                'file_id' => $file->id,
                'position' => $roomType->images()->count(),
            ]);
        });

        return redirect()->back()->with('success', 'Imagem enviada com sucesso.');
    }

    /**
     * Delete an image associated with the specified room type.
     */
    public function deleteImage(RoomType $roomType, RoomImage $image): RedirectResponse
    {
        $file = $image->file;

        DB::transaction(function () use ($image, $file) {
            if ($file && $file->storage_path) {
                Storage::disk('public')->delete($file->storage_path);
            }

            $image->delete();

            if ($file) {
                $file->delete();
            }
        });

        return redirect()->back()->with('success', 'Imagem removida com sucesso.');
    }

    /**
     * Reorder images for the specified room type.
     */
    public function reorderImages(Request $request, RoomType $roomType): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'positions' => 'required|array',
        ]);

        DB::transaction(function () use ($validated, $roomType) {
            foreach ($validated['positions'] as $imageId => $position) {
                RoomImage::where('id', $imageId)
                    ->where('room_type_id', $roomType->id)
                    ->update(['position' => $position]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Ordem das imagens atualizada com sucesso.');
    }
}
