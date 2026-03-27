<?php

namespace App\Http\Controllers;

use App\Models\Refaccion;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;

class RefaccionController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->applyOrderAreaScope($request, Refaccion::query())
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

        return response()->json(['success' => true, 'data' => Refaccion::create(SchemaPayload::forModel(new Refaccion(), $data))->load('orden')], 201);
    }

    public function show(Refaccion $refaccione)
    {
        $this->authorizeOrderArea(request(), $refaccione);

        return response()->json(['success' => true, 'data' => $refaccione->load('orden')]);
    }

    public function update(Request $request, Refaccion $refaccione)
    {
        $this->authorizeOrderArea($request, $refaccione);
        $data = $this->validatePayload($request, true);
        $this->authorizeInventoryPricingIfPresent($request, $data);
        if (array_key_exists('orden_id', $data)) {
            $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        }

        $refaccione->update(SchemaPayload::forModel($refaccione, $data));

        return response()->json(['success' => true, 'data' => $refaccione->load('orden')]);
    }

    public function destroy(Refaccion $refaccione)
    {
        $this->authorizeOrderArea(request(), $refaccione);
        $refaccione->delete();

        return response()->json(['success' => true, 'message' => 'Refaccion eliminada correctamente.']);
    }

    private function validatePayload(Request $request, bool $partial): array
    {
        return $request->validate($this->rules($partial));
    }

    private function rules(bool $partial): array
    {
        return [
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
        ];
    }
}
