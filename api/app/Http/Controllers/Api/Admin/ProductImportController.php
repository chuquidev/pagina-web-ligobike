<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductImportService;
use Illuminate\Http\Request;

class ProductImportController extends Controller
{
    public function __construct(private ProductImportService $importService) {}

    /**
     * Sube el Excel, lo interpreta y devuelve la vista previa (no guarda nada todavía).
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            $rows = $this->importService->preview($request->file('file'));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['rows' => $rows]);
    }

    /**
     * Aplica las filas que el usuario confirmó desde la vista previa.
     */
    public function commit(Request $request)
    {
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.sku' => ['required', 'string'],
            'rows.*.name' => ['required', 'string'],
            'rows.*.category' => ['required', 'string'],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
            'rows.*.stock' => ['required', 'integer', 'min:0'],
            'rows.*.brand' => ['nullable', 'string'],
            'rows.*.min_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $summary = $this->importService->commit($validated['rows']);

        return response()->json($summary);
    }
}
