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

        $this->exposePublicFileUrl($items, 'foto_path', 'foto_url');

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request, false);
        $this->storeIncomingImage($request, $data, 'foto_path', 'talleres-externos', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);

        $tallere = TallerExterno::create(SchemaPayload::forModel(new TallerExterno(), $data))->load('orden');
        $this->exposePublicFileUrl($tallere, 'foto_path', 'foto_url');

        return response()->json(['success' => true, 'data' => $tallere], 201);
    }

    public function show(TallerExterno $tallere)
    {
        $this->authorizeOrderArea(request(), $tallere);
        $tallere->load('orden');
        $this->exposePublicFileUrl($tallere, 'foto_path', 'foto_url');

        return response()->json(['success' => true, 'data' => $tallere]);
    }

    public function update(Request $request, TallerExterno $tallere)
    {
        $this->authorizeOrderArea($request, $tallere);
        $data = $this->validatePayload($request, true);
        $this->storeIncomingImage($request, $data, 'foto_path', 'talleres-externos', [
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

        $this->replaceStoredImage($tallere->foto_path, $data['foto_path'] ?? null);
        $tallere->update(SchemaPayload::forModel($tallere, $data));
        $tallere->load('orden');
        $this->exposePublicFileUrl($tallere, 'foto_path', 'foto_url');

        return response()->json(['success' => true, 'data' => $tallere]);
    }

    public function destroy(TallerExterno $tallere)
    {
        $this->authorizeOrderArea(request(), $tallere);
        $this->deleteStoredImage($tallere->foto_path);
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
            'foto_path' => 'sometimes|nullable|string|max:2048',
            'foto' => 'sometimes|nullable|image|max:5120',
            'imagen' => 'sometimes|nullable|image|max:5120',
            'image' => 'sometimes|nullable|image|max:5120',
            'evidencia' => 'sometimes|nullable|image|max:5120',
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
