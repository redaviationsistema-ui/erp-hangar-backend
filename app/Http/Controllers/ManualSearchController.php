<?php

namespace App\Http\Controllers;

use App\Models\Discrepancia;
use App\Services\ManualSearchService;
use Illuminate\Http\Request;

class ManualSearchController extends Controller
{
    public function search(Request $request, ManualSearchService $service)
    {
        $data = $request->validate([
            'query' => 'required|string',
            'manual_id' => 'sometimes|nullable|exists:manuales,id',
            'aeronave_id' => 'sometimes|nullable|exists:aeronaves,id',
            'aeronave_modelo' => 'sometimes|nullable|string|max:255',
            'revision' => 'sometimes|nullable|string|max:100',
            'tipo_manual' => 'sometimes|nullable|string|max:100',
            'estado' => 'sometimes|nullable|string|max:50',
            'ata_chapter_id' => 'sometimes|nullable|exists:ata_chapters,id',
            'ata_subchapter_id' => 'sometimes|nullable|exists:ata_subchapters,id',
            'limit' => 'sometimes|nullable|integer|min:1|max:25',
        ]);

        return response()->json([
            'success' => true,
            'data' => $service->search($data, $data['query'], $data['limit'] ?? 10),
        ]);
    }

    public function discrepancy(Discrepancia $discrepancia, ManualSearchService $service)
    {
        $this->authorizeOrderArea(request(), $discrepancia);
        $discrepancia->load('orden');

        $orden = $discrepancia->orden;
        $filters = [
            'aeronave_modelo' => $orden?->aeronave_modelo,
            'tipo_manual' => 'AMM',
            'estado' => 'vigente',
        ];

        if (! empty($orden?->ata_subchapter_id)) {
            $filters['ata_subchapter_id'] = $orden->ata_subchapter_id;
        } elseif (! empty($orden?->ata_chapter_id)) {
            $filters['ata_chapter_id'] = $orden->ata_chapter_id;
        }

        $payload = $service->contextualizeDiscrepancy(
            $discrepancia->descripcion,
            $filters
        );

        return response()->json([
            'success' => true,
            'data' => array_merge($payload, [
                'orden' => $orden ? [
                    'id' => $orden->id,
                    'folio' => $orden->folio,
                    'aeronave_modelo' => $orden->aeronave_modelo,
                    'ata_chapter_id' => $orden->ata_chapter_id,
                    'ata_subchapter_id' => $orden->ata_subchapter_id,
                ] : null,
            ]),
        ]);
    }
}
