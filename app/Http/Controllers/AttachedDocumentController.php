<?php

namespace App\Http\Controllers;

use App\Models\DocumentoAdjunto;
use App\Http\Requests\StoreAttachedDocumentRequest;
use App\Http\Requests\UpdateAttachedDocumentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AttachedDocumentController extends Controller
{
    public function index(): JsonResponse
    {
        $documentos = DocumentoAdjunto::whereHas('historiaClinica.paciente', function ($q) {
            $q->where('empresa_id', auth()->user()->empresa_id);
        })->with('historiaClinica')->get();

        return response()->json($documentos);
    }

    public function store(StoreAttachedDocumentRequest $request): JsonResponse
    {
        $archivo = $request->file('archivo');
        $ruta    = $archivo->store('documentos/' . auth()->user()->empresa_id, 'local');

        $documento = DocumentoAdjunto::create([
            'historia_clinica_id'  => $request->historia_clinica_id,
            'nombre_archivo'       => $archivo->getClientOriginalName(),
            'ruta_almacenamiento'  => $ruta,
            'tipo_mime'            => $archivo->getMimeType(),
        ]);

        return response()->json($documento, 201);
    }

    public function show(DocumentoAdjunto $documento): JsonResponse
    {
        $this->authorize('view', $documento);
        return response()->json($documento);
    }

    public function update(UpdateAttachedDocumentRequest $request, DocumentoAdjunto $documento): JsonResponse
    {
        $this->authorize('update', $documento);
        $documento->update($request->validated());
        return response()->json($documento);
    }

    public function descargar(DocumentoAdjunto $documento)
    {
        $this->authorize('view', $documento);

        abort_unless(
            Storage::disk('local')->exists($documento->ruta_almacenamiento),
            404,
            'El archivo no se encuentra en el servidor.'
        );

        return Storage::disk('local')->download(
            $documento->ruta_almacenamiento,
            $documento->nombre_archivo,
            ['Content-Type' => $documento->tipo_mime]
        );
    }

    public function destroy(DocumentoAdjunto $documento): JsonResponse
    {
        $this->authorize('delete', $documento);
        Storage::disk('local')->delete($documento->ruta_almacenamiento);
        $documento->delete();
        return response()->json(null, 204);
    }
}
