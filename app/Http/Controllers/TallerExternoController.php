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
        $this->authorizeTecnicoOnly($request);
        $data = $this->validatePayload($request, false);
        $this->authorizeOperationalPayload($request, $data, [
            'item',
            'proveedor',
            'tarea',
            'cantidad',
            'sub_componente',
            'numero_parte',
            'numero_serie',
            'foto_path',
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
            'observaciones',
            'certificado',
            'envio_a',
            'recepcion',
            'trabajo_realizado',
        ]);
        $this->storeIncomingImage($request, $data, 'foto_path', 'talleres-externos', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        $this->normalizeTablePayload($data);
        $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        $this->authorizeInventoryPricingIfPresent($request, $data);

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
        $this->authorizeOperationalPayload($request, $data, [
            'orden_id',
            'item',
            'proveedor',
            'tarea',
            'cantidad',
            'sub_componente',
            'numero_parte',
            'numero_serie',
            'foto_path',
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
            'observaciones',
            'certificado',
            'envio_a',
            'recepcion',
            'trabajo_realizado',
        ]);
        $this->storeIncomingImage($request, $data, 'foto_path', 'talleres-externos', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        $this->normalizeTablePayload($data);
        $this->authorizeInventoryPricingIfPresent($request, $data);
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
        $this->authorizeTecnicoOnly(request());
        $this->deleteStoredImage($tallere->foto_path);
        $tallere->delete();

        return response()->json(['success' => true, 'message' => 'Registro de taller externo eliminado correctamente.']);
    }

    private function validatePayload(Request $request, bool $partial): array
    {
        return $request->validate([
            'orden_id' => ($partial ? 'sometimes' : 'required') . '|exists:ordenes,id',
            'item' => 'sometimes|nullable|string|max:20',
            'proveedor' => 'sometimes|nullable|string|max:255',
            'tarea' => 'sometimes|nullable|string|max:255',
            'cantidad' => 'sometimes|nullable|integer|min:1',
            'sub_componente' => 'sometimes|nullable|string|max:255',
            'numero_parte' => 'sometimes|nullable|string|max:255',
            'numero_serie' => 'sometimes|nullable|string|max:255',
            'foto_path' => 'sometimes|nullable',
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

    private function normalizeTablePayload(array &$data): void
    {
        if (! array_key_exists('proveedor', $data) || trim((string) $data['proveedor']) === '') {
            $data['proveedor'] = trim((string) ($data['envio_a'] ?? $data['tarea'] ?? 'EXTERNO'));
        }

        if (! array_key_exists('trabajo_realizado', $data) || trim((string) $data['trabajo_realizado']) === '') {
            $data['trabajo_realizado'] = trim((string) ($data['observaciones'] ?? $data['tarea'] ?? ''));
        }
    }
}
