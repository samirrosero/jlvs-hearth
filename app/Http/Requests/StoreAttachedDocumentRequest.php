<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachedDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'historia_clinica_id' => ['required', 'exists:historias_clinicas,id'],
            'archivo'             => [
                'required', 'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Debes seleccionar un archivo para subir.',
            'archivo.mimes'    => 'Formato no permitido. Solo se aceptan: PDF, imágenes (JPG, PNG), Word (DOC, DOCX) y Excel (XLS, XLSX).',
            'archivo.max'      => 'El archivo excede el tamaño máximo permitido de 10 MB.',
        ];
    }
}
