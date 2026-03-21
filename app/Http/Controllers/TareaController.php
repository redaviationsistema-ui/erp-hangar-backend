<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use Illuminate\Http\Request;

class TareaController extends Controller
{
    /**
     * 📄 Mostrar todas las tareas
     */
    public function index()
    {
        return Tarea::with('orden')->get();
    }

    /**
     * 💾 Crear una nueva tarea
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_id' => 'required|exists:ordenes,id',
            'descripcion' => 'required|string',
        ]);

        $tarea = Tarea::create($data);

        return response()->json($tarea, 201);
    }

    /**
     * 🔍 Mostrar una tarea específica
     */
    public function show($id)
    {
        return Tarea::with('orden')->findOrFail($id);
    }

    /**
     * ✏️ Actualizar una tarea
     */
    public function update(Request $request, $id)
    {
        $tarea = Tarea::findOrFail($id);

        $data = $request->validate([
            'descripcion' => 'sometimes|string',
        ]);

        $tarea->update($data);

        return response()->json($tarea);
    }

    /**
     * 🗑️ Eliminar una tarea
     */
    public function destroy($id)
    {
        $tarea = Tarea::findOrFail($id);
        $tarea->delete();

        return response()->json([
            'message' => 'Tarea eliminada correctamente'
        ]);
    }
}