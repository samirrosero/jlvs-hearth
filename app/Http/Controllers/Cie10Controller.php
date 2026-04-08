<?php

namespace App\Http\Controllers;

use App\Models\Cie10;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Cie10Controller extends Controller
{
    /**
     * Búsqueda de códigos CIE-10 para el selector en la historia clínica.
     * GET /cie10?buscar=diabetes  →  devuelve hasta 20 coincidencias
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cie10::query();

        if ($request->filled('buscar')) {
            $termino = $request->input('buscar');
            $query->where(function ($q) use ($termino) {
                $q->where('codigo', 'like', "%{$termino}%")
                  ->orWhere('descripcion', 'like', "%{$termino}%");
            });
        }

        return response()->json($query->limit(20)->get());
    }
}
