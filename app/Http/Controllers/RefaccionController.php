<?php

namespace App\Http\Controllers;

use App\Models\Refaccion;
use Illuminate\Http\Request;

class RefaccionController extends Controller
{
    /**
     * 📄 Listar refacciones
     */
    public function index()
    {
        return Refaccion::with('orden')->get();
    }

    /**
     * 💾 Crear refacción
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'nombre' => 'required|string',
            'cantidad' => 'required|numeric',
        ]);

        $item = Refaccion::create($data);

        return response()->json($item, 201);
    }

    /**
     * 🔍 Mostrar refacción
     */
    public function show($id)
    {
        return Refaccion::with('orden')->findOrFail($id);
    }

    /**
     * ✏️ Actualizar refacción
     */
    public function update(Request $request, $id)
    {
        $item = Refaccion::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|string',
            'cantidad' => 'sometimes|numeric',
        ]);

        $item->update($data);

        return response()->json($item);
    }

    /**
     * 🗑️ Eliminar refacción
     */
    public function destroy($id)
    {
        $item = Refaccion::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Refacción eliminada'
        ]);
    }
}