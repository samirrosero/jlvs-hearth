<?php

namespace App\Http\Controllers;

use App\Models\LogAuditoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogAuditoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $query = LogAuditoria::where('empresa_id', $empresaId)
            ->orderByDesc('created_at');

        // Filtros opcionales
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->integer('usuario_id'));
        }
        if ($request->filled('accion')) {
            $query->where('accion', $request->string('accion'));
        }
        if ($request->filled('modelo')) {
            $query->where('modelo', $request->string('modelo'));
        }
        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->string('desde'));
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->string('hasta'));
        }

        return response()->json($query->with('usuario')->paginate(50));
    }

    public function show(LogAuditoria $log): JsonResponse
    {
        // Solo el admin de la misma empresa puede ver el detalle
        abort_if($log->empresa_id !== auth()->user()->empresa_id, 403);

        return response()->json($log->load('usuario'));
    }
}
