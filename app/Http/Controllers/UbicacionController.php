<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UbicacionController extends Controller
{
    // Dataset DIVIPOLA — DANE — datos.gov.co (gdxc-w37w)
    // Columnas: cod_dpto, dpto, cod_mpio, nom_mpio
    private const API_URL = 'https://www.datos.gov.co/resource/gdxc-w37w.json';
    private const TTL     = 86400; // 24 horas

    public function departamentos(): JsonResponse
    {
        $data = Cache::remember('divipola_departamentos', self::TTL, function () {
            $response = Http::timeout(15)->get(self::API_URL, [
                '$select' => 'cod_dpto,dpto',
                '$group'  => 'cod_dpto,dpto',
                '$order'  => 'dpto ASC',
                '$limit'  => '100',
            ]);

            if (!$response->successful()) {
                return [];
            }

            return collect($response->json())
                ->filter(fn ($d) => !empty($d['dpto']))
                ->map(fn ($d) => [
                    'codigo' => $d['cod_dpto'] ?? '',
                    'nombre' => mb_convert_case(strtolower($d['dpto']), MB_CASE_TITLE, 'UTF-8'),
                ])
                ->sortBy('nombre')
                ->values()
                ->toArray();
        });

        return response()->json($data);
    }

    public function municipios(string $codigo): JsonResponse
    {
        $codigo = preg_replace('/[^0-9]/', '', $codigo);

        $data = Cache::remember("divipola_municipios_{$codigo}", self::TTL, function () use ($codigo) {
            $response = Http::timeout(15)->get(self::API_URL, [
                '$where'  => "cod_dpto='{$codigo}'",
                '$select' => 'nom_mpio',
                '$order'  => 'nom_mpio ASC',
                '$limit'  => '500',
            ]);

            if (!$response->successful()) {
                return [];
            }

            return collect($response->json())
                ->filter(fn ($m) => !empty($m['nom_mpio']))
                ->map(fn ($m) => mb_convert_case(strtolower($m['nom_mpio']), MB_CASE_TITLE, 'UTF-8'))
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        });

        return response()->json($data);
    }
}
