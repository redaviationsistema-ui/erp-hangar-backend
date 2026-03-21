<?php

namespace App\Http\Controllers;

use App\Models\Herramienta;
use Illuminate\Http\Request;

class HerramientaController extends Controller
{
    /**
     * 📄 Listar todas las herramientas
     */
    public function index()
    {
        return Herramienta::with('orden')->get();
    }

    /**
     * 💾 Crear una herramienta
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'nombre' => 'required|string',
        ]);

        $item = Herramienta::create($data);

        return response()->json($item, 201);
    }

    /**
     * 🔍 Mostrar una herramienta específica
     */
    public function show($id)
    {
        return Herramienta::with('orden')->findOrFail($id);
    }

    /**
     * ✏️ Actualizar herramienta
     */
    public function update(Request $request, $id)
    {
        $item = Herramienta::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|string',
        ]);

        $item->update($data);

        return response()->json($item);
    }

    /**
     * 🗑️ Eliminar herramienta
     */
    public function destroy($id)
    {
        $item = Herramienta::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Herramienta eliminada'
        ]);
    }
}