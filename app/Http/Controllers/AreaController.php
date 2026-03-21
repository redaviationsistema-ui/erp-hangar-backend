<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * 📄 LISTAR TODAS LAS ÁREAS
     */
    public function index()
    {
        return response()->json(Area::all());
    }

    /**
     * 💾 CREAR NUEVA ÁREA
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string|unique:areas,codigo'
        ]);

        $area = Area::create($request->all());

        return response()->json($area, 201);
    }

    /**
     * 🔍 VER UNA ÁREA
     */
    public function show($id)
    {
        $area = Area::findOrFail($id);

        return response()->json($area);
    }

    /**
     * ✏️ ACTUALIZAR ÁREA
     */
    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string',
            'codigo' => 'required|string|unique:areas,codigo,' . $id
        ]);

        $area->update($request->all());

        return response()->json($area);
    }

    /**
     * 🗑 ELIMINAR ÁREA
     */
    public function destroy($id)
    {
        $area = Area::findOrFail($id);
        $area->delete();

        return response()->json([
            'message' => 'Área eliminada correctamente'
        ]);
    }
}