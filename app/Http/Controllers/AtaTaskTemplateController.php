<?php

namespace App\Http\Controllers;

use App\Models\AtaSubchapter;
use App\Models\AtaTaskTemplate;
use Illuminate\Http\Request;

class AtaTaskTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = $this->applyAreaScope($request, AtaTaskTemplate::query())
            ->with(['subchapter.chapter', 'area'])
            ->when($request->filled('ata_subchapter_id'), fn ($q) => $q->where('ata_subchapter_id', $request->integer('ata_subchapter_id')))
            ->orderBy('titulo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    public function getBySubAta(AtaSubchapter $subchapter)
    {
        return response()->json([
            'success' => true,
            'data' => $this->applyAreaScope(request(), $subchapter->tasks())->with('area')->orderBy('titulo')->get(),
        ]);
    }
}
