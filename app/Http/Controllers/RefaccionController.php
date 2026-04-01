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
        $this->exposePublicFileUrl($items, 'certificado_conformidad_imagen', 'certificado_conformidad_imagen_url');

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request, false);
        $this->storeIncomingImage($request, $data, 'certificado_conformidad_imagen', 'refacciones', [
            'certificado_conformidad_imagen',
            'certificado_conformidad_imagen_base64',
            'certificado_conformidad_foto',
            'certificado_conformidad_imagen_file',
        ]);
        $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        $this->authorizeInventoryPricingIfPresent($request, $data);

        $item = Refaccion::create(SchemaPayload::forModel(new Refaccion(), $data))->load('orden');
        $this->exposePublicFileUrl($item, 'certificado_conformidad_imagen', 'certificado_conformidad_imagen_url');

        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function show(Refaccion $refaccione)
    {
        $this->authorizeOrderArea(request(), $refaccione);
        $this->exposePublicFileUrl($refaccione, 'certificado_conformidad_imagen', 'certificado_conformidad_imagen_url');

        return response()->json(['success' => true, 'data' => $refaccione->load('orden')]);
    }

    public function update(Request $request, Refaccion $refaccione)
    {
        $this->authorizeOrderArea($request, $refaccione);
        $data = $this->validatePayload($request, true);
        $this->storeIncomingImage($request, $data, 'certificado_conformidad_imagen', 'refacciones', [
            'certificado_conformidad_imagen',
            'certificado_conformidad_imagen_base64',
            'certificado_conformidad_foto',
            'certificado_conformidad_imagen_file',
        ]);
        $this->authorizeInventoryPricingIfPresent($request, $data);
        if (array_key_exists('orden_id', $data)) {
            $this->authorizeAreaId($request, \App\Models\Orden::findOrFail($data['orden_id'])->area_id);
        }

        $this->replaceStoredImage(
            $refaccione->certificado_conformidad_imagen,
            $data['certificado_conformidad_imagen'] ?? $refaccione->certificado_conformidad_imagen,
        );
        $refaccione->update(SchemaPayload::forModel($refaccione, $data));
        $this->exposePublicFileUrl($refaccione, 'certificado_conformidad_imagen', 'certificado_conformidad_imagen_url');

        return response()->json(['success' => true, 'data' => $refaccione->load('orden')]);
    }

    public function destroy(Refaccion $refaccione)
    {
        $this->authorizeOrderArea(request(), $refaccione);
        $this->deleteStoredImage($refaccione->certificado_conformidad_imagen);
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
            'solicitante_nombre' => 'sometimes|nullable|string|max:255',
            'nombre' => 'sometimes|nullable|string|max:255',
            'descripcion' => ($partial ? 'sometimes' : 'required') . '|string',
            'cantidad' => 'sometimes|nullable|integer|min:1',
            'numero_parte' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|nullable|string|max:100',
            'certificado_conformidad' => 'sometimes|nullable|string|max:255',
            'certificado_conformidad_imagen' => 'sometimes|nullable|string|max:2048',
            'certificado_conformidad_foto' => 'sometimes|nullable|image|max:5120',
            'certificado_conformidad_imagen_base64' => 'sometimes|nullable|string',
            'certificado_conformidad_imagen_file' => 'sometimes|nullable|image|max:5120',
            'area_procedencia' => 'sometimes|nullable|string|max:255',
            'recibe_fecha' => 'sometimes|nullable|date',
            'recibe_nombre' => 'sometimes|nullable|string|max:255',
            'costo_total' => 'sometimes|nullable|numeric',
            'precio_venta' => 'sometimes|nullable|numeric',
        ];
    }
}
