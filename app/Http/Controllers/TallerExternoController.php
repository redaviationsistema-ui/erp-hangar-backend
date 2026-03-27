<?php

namespace App\Http\Controllers;

use App\Models\TallerExterno;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;

class TallerExternoController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->applyOrderAreaScope($request, TallerExterno::query())
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

        return response()->json(['success' => true, 'data' => TallerExterno::create(SchemaPayload::forModel(new TallerExterno(), $data))->load('orden')], 201);
    }

    public function show(TallerExterno $tallere)
    {
        $this->authorizeOrderArea(request(), $tallere);

        return response()->json(['success' => true, 'data' => $tallere->load('orden')]);
    }

    public function update(Request $request, TallerExterno $tallere)
    {
        $this->authorizeOrderArea($request, $tallere);
        $data = $this->validatePayload($request, true);
        if (array_key_exists('orden_id', $data)) {
            $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        }

        $tallere->update(SchemaPayload::forModel($tallere, $data));

        return response()->json(['success' => true, 'data' => $tallere->load('orden')]);
    }

    public function destroy(TallerExterno $tallere)
    {
        $this->authorizeOrderArea(request(), $tallere);
        $tallere->delete();

        return response()->json(['success' => true, 'message' => 'Registro de taller externo eliminado correctamente.']);
    }

    private function validatePayload(Request $request, bool $partial): array
    {
        return $request->validate([
            'orden_id' => ($partial ? 'sometimes' : 'required') . '|exists:ordenes,id',
            'item' => 'sometimes|nullable|string|max:20',
            'proveedor' => ($partial ? 'sometimes' : 'required') . '|string|max:255',
            'tarea' => 'sometimes|nullable|string|max:255',
            'cantidad' => 'sometimes|nullable|integer|min:1',
            'sub_componente' => 'sometimes|nullable|string|max:255',
            'numero_parte' => 'sometimes|nullable|string|max:255',
            'numero_serie' => 'sometimes|nullable|string|max:255',
            'foto_path' => 'sometimes|nullable|string|max:255',
            'observaciones' => 'sometimes|nullable|string',
            'certificado' => 'sometimes|nullable|string|max:255',
            'envio_a' => 'sometimes|nullable|string|max:255',
            'recepcion' => 'sometimes|nullable|string|max:255',
            'trabajo_realizado' => 'sometimes|nullable|string',
            'costo' => 'sometimes|nullable|numeric',
            'precio_venta' => 'sometimes|nullable|numeric',
        ]);
    }
}
