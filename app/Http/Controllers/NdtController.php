<?php

namespace App\Http\Controllers;

use App\Models\Ndt;
use App\Support\SchemaPayload;
use Illuminate\Http\Request;

class NdtController extends Controller
{
    public function index(Request $request)
    {
        $items = $this->applyOrderAreaScope($request, Ndt::query())
            ->with('orden')
            ->when($request->filled('orden_id'), fn ($q) => $q->where('orden_id', $request->integer('orden_id')))
            ->orderBy('id')
            ->get();

        $this->exposePublicFileUrl($items, 'evidencia_path', 'evidencia_url');

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request, false);
        $this->storeIncomingImage($request, $data, 'evidencia_path', 'ndt', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);

        $ndt = Ndt::create(SchemaPayload::forModel(new Ndt(), $data))->load('orden');
        $this->exposePublicFileUrl($ndt, 'evidencia_path', 'evidencia_url');

        return response()->json(['success' => true, 'data' => $ndt], 201);
    }

    public function show(Ndt $ndt)
    {
        $this->authorizeOrderArea(request(), $ndt);
        $ndt->load('orden');
        $this->exposePublicFileUrl($ndt, 'evidencia_path', 'evidencia_url');

        return response()->json(['success' => true, 'data' => $ndt]);
    }

    public function update(Request $request, Ndt $ndt)
    {
        $this->authorizeOrderArea($request, $ndt);
        $data = $this->validatePayload($request, true);
        $this->storeIncomingImage($request, $data, 'evidencia_path', 'ndt', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        if (array_key_exists('orden_id', $data)) {
            $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        }

        $this->replaceStoredImage($ndt->evidencia_path, $data['evidencia_path'] ?? null);
        $ndt->update(SchemaPayload::forModel($ndt, $data));
        $ndt->load('orden');
        $this->exposePublicFileUrl($ndt, 'evidencia_path', 'evidencia_url');

        return response()->json(['success' => true, 'data' => $ndt]);
    }

    public function destroy(Ndt $ndt)
    {
        $this->authorizeOrderArea(request(), $ndt);
        $this->deleteStoredImage($ndt->evidencia_path);
        $ndt->delete();

        return response()->json(['success' => true, 'message' => 'Registro NDT eliminado correctamente.']);
    }

    private function validatePayload(Request $request, bool $partial): array
    {
        return $request->validate([
            'orden_id' => ($partial ? 'sometimes' : 'required') . '|exists:ordenes,id',
            'item' => 'sometimes|nullable|string|max:20',
            'tipo_prueba' => ($partial ? 'sometimes' : 'required') . '|string|max:255',
            'cantidad' => 'sometimes|nullable|integer|min:1',
            'sub_componente' => 'sometimes|nullable|string|max:255',
            'numero_parte' => 'sometimes|nullable|string|max:255',
            'numero_serie' => 'sometimes|nullable|string|max:255',
            'evidencia_path' => 'sometimes|nullable|string|max:2048',
            'foto' => 'sometimes|nullable|image|max:5120',
            'imagen' => 'sometimes|nullable|image|max:5120',
            'image' => 'sometimes|nullable|image|max:5120',
            'evidencia' => 'sometimes|nullable|image|max:5120',
            'seccion_manual' => 'sometimes|nullable|string|max:255',
            'certificado' => 'sometimes|nullable|string|max:255',
            'envio_a' => 'sometimes|nullable|string|max:255',
            'recepcion' => 'sometimes|nullable|string|max:255',
            'costo_total' => 'sometimes|nullable|numeric',
            'precio_venta' => 'sometimes|nullable|numeric',
            'resultado' => 'sometimes|nullable|string|max:255',
        ]);
    }
}
