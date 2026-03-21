<?php

namespace App\Http\Controllers;

use App\Models\Ndt;
use Illuminate\Http\Request;

class NdtController extends Controller
{
    /**
     * 📄 Listar pruebas NDT
     */
    public function index()
    {
        return Ndt::with('orden')->get();
    }

    /**
     * 💾 Crear registro NDT
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'tipo' => 'required|string',
        ]);

        $item = Ndt::create($data);

        return response()->json($item, 201);
    }

    /**
     * 🔍 Mostrar NDT
     */
    public function show($id)
    {
        return Ndt::with('orden')->findOrFail($id);
    }

    /**
     * ✏️ Actualizar NDT
     */
    public function update(Request $request, $id)
    {
        $item = Ndt::findOrFail($id);

        $data = $request->validate([
            'tipo' => 'sometimes|string',
        ]);

        $item->update($data);

        return response()->json($item);
    }

    /**
     * 🗑️ Eliminar NDT
     */
    public function destroy($id)
    {
        $item = Ndt::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Registro NDT eliminado'
        ]);
    }
}