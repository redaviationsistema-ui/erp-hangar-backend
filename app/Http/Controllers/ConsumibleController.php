<?php

namespace App\Http\Controllers;

use App\Models\Consumible;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;

class ConsumibleController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->applyOrderAreaScope($request, Consumible::query())
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
        $this->authorizeInventoryPricingIfPresent($request, $data);

        return response()->json(['success' => true, 'data' => Consumible::create(SchemaPayload::forModel(new Consumible(), $data))->load('orden')], 201);
    }

    public function show(Consumible $consumible)
    {
        $this->authorizeOrderArea(request(), $consumible);

        return response()->json(['success' => true, 'data' => $consumible->load('orden')]);
    }

    public function update(Request $request, Consumible $consumible)
    {
        $this->authorizeOrderArea($request, $consumible);
        $data = $this->validatePayload($request, true);
        $this->authorizeInventoryPricingIfPresent($request, $data);
        if (array_key_exists('orden_id', $data)) {
            $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        }

        $consumible->update(SchemaPayload::forModel($consumible, $data));

        return response()->json(['success' => true, 'data' => $consumible->load('orden')]);
    }

    public function destroy(Consumible $consumible)
    {
        $this->authorizeOrderArea(request(), $consumible);
        $consumible->delete();

        return response()->json(['success' => true, 'message' => 'Consumible eliminado correctamente.']);
    }

    private function validatePayload(Request $request, bool $partial): array
    {
        return $request->validate([
            'orden_id' => ($partial ? 'sometimes' : 'required') . '|exists:ordenes,id',
            'item' => 'sometimes|nullable|string|max:20',
            'solicitante_fecha' => 'sometimes|nullable|date',
            'nombre' => 'sometimes|nullable|string|max:255',
            'descripcion' => ($partial ? 'sometimes' : 'required') . '|string',
            'cantidad' => 'sometimes|nullable|integer|min:1',
            'numero_parte' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|nullable|string|max:100',
            'certificado_conformidad' => 'sometimes|nullable|string|max:255',
            'area_procedencia' => 'sometimes|nullable|string|max:255',
            'recibe_fecha' => 'sometimes|nullable|date',
            'costo_total' => 'sometimes|nullable|numeric',
            'precio_venta' => 'sometimes|nullable|numeric',
        ]);
    }
}
