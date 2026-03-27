<?php

namespace App\Http\Controllers;

use App\Models\Medicion;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;

class MedicionController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->applyOrderAreaScope($request, Medicion::query())
            ->with('orden')
            ->when($request->filled('orden_id'), fn ($q) => $q->where('orden_id', $request->integer('orden_id')))
            ->orderBy('id')
            ->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request, false);
        $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);

        return response()->json(['success' => true, 'data' => Medicion::create(SchemaPayload::forModel(new Medicion(), $data))->load('orden')], 201);
    }

    public function show(Medicion $medicione)
    {
        $this->authorizeOrderArea(request(), $medicione);

        return response()->json(['success' => true, 'data' => $medicione->load('orden')]);
    }

    public function update(Request $request, Medicion $medicione)
    {
        $this->authorizeOrderArea($request, $medicione);
        $data = $this->validatePayload($request, true);
        if (array_key_exists('orden_id', $data)) {
            $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        }

        $medicione->update(SchemaPayload::forModel($medicione, $data));

        return response()->json(['success' => true, 'data' => $medicione->load('orden')]);
    }

    public function destroy(Medicion $medicione)
    {
        $this->authorizeOrderArea(request(), $medicione);
        $medicione->delete();

        return response()->json(['success' => true, 'message' => 'Medicion eliminada correctamente.']);
    }

    private function validatePayload(Request $request, bool $partial): array
    {
        return $request->validate([
            'orden_id' => ($partial ? 'sometimes' : 'required') . '|exists:ordenes,id',
            'item' => 'sometimes|nullable|string|max:20',
            'tecnico' => 'sometimes|nullable|string|max:255',
            'descripcion' => ($partial ? 'sometimes' : 'required') . '|string',
            'manual_od' => 'sometimes|nullable|string|max:255',
            'manual_id' => 'sometimes|nullable|string|max:255',
            'resultado_od' => 'sometimes|nullable|string|max:255',
            'resultado_id' => 'sometimes|nullable|string|max:255',
            'imagen_path' => 'sometimes|nullable|string|max:255',
            'observaciones' => 'sometimes|nullable|string',
            'parametro' => 'sometimes|nullable|string|max:255',
            'valor' => 'sometimes|nullable|string|max:255',
            'unidad' => 'sometimes|nullable|string|max:255',
        ]);
    }
}
