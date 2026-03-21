<?php

namespace App\Http\Controllers;

use App\Models\Discrepancia;
use Illuminate\Http\Request;

class DiscrepanciaController extends Controller
{
    /**
     * 📄 Listar todas las discrepancias
     */
    public function index()
    {
        return Discrepancia::with('orden')->get();
    }

    /**
     * 💾 Crear nueva discrepancia
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'descripcion' => 'required|string',
        ]);

        $item = Discrepancia::create($data);

        return response()->json($item, 201);
    }

    /**
     * 🔍 Mostrar una discrepancia
     */
    public function show($id)
    {
        return Discrepancia::with('orden')->findOrFail($id);
    }

    /**
     * ✏️ Actualizar discrepancia
     */
    public function update(Request $request, $id)
    {
        $item = Discrepancia::findOrFail($id);

        $data = $request->validate([
            'descripcion' => 'sometimes|string',
        ]);

        $item->update($data);

        return response()->json($item);
    }

    /**
     * 🗑️ Eliminar discrepancia
     */
    public function destroy($id)
    {
        $item = Discrepancia::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => 'Discrepancia eliminada'
        ]);
    }
}