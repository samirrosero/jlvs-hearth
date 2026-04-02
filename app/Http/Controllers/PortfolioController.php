<?php

namespace App\Http\Controllers;

use App\Models\Portafolio;
use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use Illuminate\Http\JsonResponse;

class PortfolioController extends Controller
{
    public function index(): JsonResponse
    {
        $portafolios = Portafolio::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre_convenio')
            ->get();
        return response()->json($portafolios);
    }

    public function store(StorePortfolioRequest $request): JsonResponse
    {
        $portafolio = Portafolio::create(array_merge(
            $request->validated(),
            ['empresa_id' => auth()->user()->empresa_id]
        ));
        return response()->json($portafolio, 201);
    }

    public function show(Portafolio $portafolio): JsonResponse
    {
        $this->authorize('view', $portafolio);
        return response()->json($portafolio);
    }

    public function update(UpdatePortfolioRequest $request, Portafolio $portafolio): JsonResponse
    {
        $this->authorize('update', $portafolio);
        $portafolio->update($request->validated());
        return response()->json($portafolio);
    }

    public function destroy(Portafolio $portafolio): JsonResponse
    {
        $this->authorize('delete', $portafolio);
        $portafolio->delete();
        return response()->json(null, 204);
    }
}
