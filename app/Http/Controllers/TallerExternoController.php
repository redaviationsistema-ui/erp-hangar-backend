<?php

namespace App\Http\Controllers;

use App\Models\TallerExterno;
use Illuminate\Http\Request;

class TallerExternoController extends Controller
{
    /**
     * 📄 Listar talleres externos
     */
    public function index()
    {
        return TallerExterno::with('orden')->get();
    }

    /**
     * 💾 Crear registro de taller externo
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'nombre' => 'required|string',
        ]);

        $item = TallerExterno::create($data);

        return response()->json($item, 201);
    }

    /**
     * 🔍 Mostrar taller externo
     */
    public function show($id)
    {
        return TallerExterno::with('orden')->findOrFail($id);
    }

    /**
     * ✏️ Actualizar taller externo
     */
    public function update(Request $request, $id)
    {
        $item = TallerExterno::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|string',
        ]);

        $item->update($data);

        return response()->json($item);
    }

    /**
     * 🗑️ Eliminar taller externo
     */
    public function destroy($id)
    {
        $item = TallerExterno::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Taller externo eliminado'
        ]);
    }
}