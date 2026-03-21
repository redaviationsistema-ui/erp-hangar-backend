<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Illuminate\Http\Request;
use App\Http\Resources\OrdenResource;

class OrdenController extends Controller
{
    /**
     * 📄 Listado con filtros + paginación
     */
    public function index(Request $request)
    {
        $query = Orden::with(['tipo', 'usuario']);

        // 🔍 FILTROS DINÁMICOS
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tipo_id')) {
            $query->where('tipo_id', $request->tipo_id);
        }

        if ($request->has('folio')) {
            $query->where('folio', 'like', '%' . $request->folio . '%');
        }

        // 📦 PAGINACIÓN
        $ordenes = $query->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Órdenes obtenidas',
            'data' => OrdenResource::collection($ordenes),
            'meta' => [
                'current_page' => $ordenes->currentPage(),
                'last_page' => $ordenes->lastPage(),
                'total' => $ordenes->total(),
            ]
        ]);
    }

    /**
     * 💾 Crear orden
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_id' => 'required|exists:tipo_ordens,id',
            'user_id' => 'required|exists:users,id',
            'folio' => 'required|string|unique:ordenes,folio',
            'descripcion' => 'required|string',
            'estado' => 'required|string',
        ]);

        $orden = Orden::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Orden creada',
            'data' => new OrdenResource($orden)
        ], 201);
    }

    /**
     * 🔍 Orden simple
     */
    public function show($id)
    {
        $orden = Orden::with(['tipo', 'usuario'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Orden encontrada',
            'data' => new OrdenResource($orden)
        ]);
    }

    /**
     * ✏️ Actualizar
     */
    public function update(Request $request, $id)
    {
        $orden = Orden::findOrFail($id);

        $data = $request->validate([
            'tipo_id' => 'sometimes|exists:tipo_ordens,id',
            'descripcion' => 'sometimes|string',
            'estado' => 'sometimes|string',
        ]);

        $orden->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizada',
            'data' => new OrdenResource($orden)
        ]);
    }

    /**
     * 🗑️ Eliminar
     */
    public function destroy($id)
    {
        $orden = Orden::findOrFail($id);
        $orden->delete();

        return response()->json([
            'success' => true,
            'message' => 'Orden eliminada'
        ]);
    }

    /**
     * 🔥 ENDPOINT MAESTRO PRO
     */
    public function showCompleto($id)
    {
        $orden = Orden::with([
            'tipo',
            'usuario',
            'detalles',
            'tareas',
            'discrepancias',
            'refacciones',
            'consumibles',
            'herramientas',
            'ndt',
            'talleresExternos',
            'mediciones'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Orden completa',
            'data' => new OrdenResource($orden)
        ]);
    }
}