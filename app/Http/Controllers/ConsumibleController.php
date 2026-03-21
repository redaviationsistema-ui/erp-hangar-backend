<?php

namespace App\Http\Controllers;

use App\Models\Consumible;
use Illuminate\Http\Request;

class ConsumibleController extends Controller
{
    /**
     * 📄 Listar consumibles
     */
    public function index()
    {
        return Consumible::with('orden')->get();
    }

    /**
     * 💾 Crear consumible
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'nombre' => 'required|string',
            'cantidad' => 'required|numeric',
        ]);

        $item = Consumible::create($data);

        return response()->json($item, 201);
    }

    /**
     * 🔍 Mostrar consumible
     */
    public function show($id)
    {
        return Consumible::with('orden')->findOrFail($id);
    }

    /**
     * ✏️ Actualizar consumible
     */
    public function update(Request $request, $id)
    {
        $item = Consumible::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'cantidad' => 'sometimes|numeric',
        ]);

        $item->update($data);

        return response()->json($item);
    }

    /**
     * 🗑️ Eliminar consumible
     */
    public function destroy($id)
    {
        $item = Consumible::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Consumible eliminado'
        ]);
    }
}