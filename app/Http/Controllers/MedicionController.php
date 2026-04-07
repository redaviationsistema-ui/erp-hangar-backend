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

        $this->exposePublicFileUrl($items, 'imagen_path', 'imagen_url');

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request, false);
        $this->authorizeTecnicoOnly($request);
        $this->authorizeOrderAreaCodeAllowed($request, (int) $data['orden_id'], ['TREN', 'FREN']);
        $this->storeIncomingImage($request, $data, 'imagen_path', 'mediciones', [
            'foto',
            'imagen',
            'image',
            'evidencia',
            'foto_base64',
            'imagen_base64',
            'evidencia_base64',
        ]);
        $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);

        $medicione = Medicion::create(SchemaPayload::forModel(new Medicion(), $data))->load('orden');
        $this->exposePublicFileUrl($medicione, 'imagen_path', 'imagen_url');

        return response()->json(['success' => true, 'data' => $medicione], 201);
    }

    public function show(Medicion $medicione)
    {
        $this->authorizeOrderArea(request(), $medicione);
        $medicione->load('orden');
        $this->exposePublicFileUrl($medicione, 'imagen_path', 'imagen_url');

        return response()->json(['success' => true, 'data' => $medicione]);
    }

    public function update(Request $request, Medicion $medicione)
    {
        $this->authorizeOrderArea($request, $medicione);
        $data = $this->validatePayload($request, true);
        $this->authorizeTecnicoOnly($request);
        $this->authorizeOrderAreaCodeAllowed($request, (int) ($data['orden_id'] ?? $medicione->orden_id), ['TREN', 'FREN']);
        $this->storeIncomingImage($request, $data, 'imagen_path', 'mediciones', [
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

        $this->replaceStoredImage($medicione->imagen_path, $data['imagen_path'] ?? null);
        $medicione->update(SchemaPayload::forModel($medicione, $data));
        $medicione->load('orden');
        $this->exposePublicFileUrl($medicione, 'imagen_path', 'imagen_url');

        return response()->json(['success' => true, 'data' => $medicione]);
    }

    public function destroy(Medicion $medicione)
    {
        $this->authorizeOrderArea(request(), $medicione);
        $this->authorizeTecnicoOnly(request());
        $this->deleteStoredImage($medicione->imagen_path);
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
            'resultado_od' => 'sometimes|nullable|in:DENTRO DE PARAMETROS,FUERA DE PARAMETROS',
            'resultado_id' => 'sometimes|nullable|in:DENTRO DE PARAMETROS,FUERA DE PARAMETROS',
            'imagen_path' => 'sometimes|nullable|string|max:2048',
            'foto' => 'sometimes|nullable|image|max:5120',
            'imagen' => 'sometimes|nullable|image|max:5120',
            'image' => 'sometimes|nullable|image|max:5120',
            'evidencia' => 'sometimes|nullable|image|max:5120',
            'observaciones' => 'sometimes|nullable|string',
            'parametro' => 'sometimes|nullable|string|max:255',
            'valor' => 'sometimes|nullable|string|max:255',
            'unidad' => 'sometimes|nullable|string|max:255',
        ]);
    }
}
