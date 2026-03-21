<?php

namespace App\Http\Controllers;

use App\Models\Medicion;
use Illuminate\Http\Request;

class MedicionController extends Controller
{
    /**
     * 📄 Listar mediciones
     */
    public function index()
    {
        return Medicion::with('orden')->get();
    }

    /**
     * 💾 Crear medición
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'valor' => 'required|numeric',
        ]);

        $item = Medicion::create($data);

        return response()->json($item, 201);
    }

    /**
     * 🔍 Mostrar medición
     */
    public function show($id)
    {
        return Medicion::with('orden')->findOrFail($id);
    }

    /**
     * ✏️ Actualizar medición
     */
    public function update(Request $request, $id)
    {
        $item = Medicion::findOrFail($id);

        $data = $request->validate([
            'valor' => 'sometimes|numeric',
        ]);

        $item->update($data);

        return response()->json($item);
    }

    /**
     * 🗑️ Eliminar medición
     */
    public function destroy($id)
    {
        $item = Medicion::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Medición eliminada'
        ]);
    }
}