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

    private const DEPARTAMENTOS_FALLBACK = [
        ['codigo' => '91', 'nombre' => 'Amazonas'],
        ['codigo' => '05', 'nombre' => 'Antioquia'],
        ['codigo' => '81', 'nombre' => 'Arauca'],
        ['codigo' => '08', 'nombre' => 'Atlántico'],
        ['codigo' => '11', 'nombre' => 'Bogotá D.C.'],
        ['codigo' => '13', 'nombre' => 'Bolívar'],
        ['codigo' => '15', 'nombre' => 'Boyacá'],
        ['codigo' => '17', 'nombre' => 'Caldas'],
        ['codigo' => '18', 'nombre' => 'Caquetá'],
        ['codigo' => '85', 'nombre' => 'Casanare'],
        ['codigo' => '19', 'nombre' => 'Cauca'],
        ['codigo' => '20', 'nombre' => 'Cesar'],
        ['codigo' => '27', 'nombre' => 'Chocó'],
        ['codigo' => '23', 'nombre' => 'Córdoba'],
        ['codigo' => '25', 'nombre' => 'Cundinamarca'],
        ['codigo' => '94', 'nombre' => 'Guainía'],
        ['codigo' => '95', 'nombre' => 'Guaviare'],
        ['codigo' => '41', 'nombre' => 'Huila'],
        ['codigo' => '44', 'nombre' => 'La Guajira'],
        ['codigo' => '47', 'nombre' => 'Magdalena'],
        ['codigo' => '50', 'nombre' => 'Meta'],
        ['codigo' => '52', 'nombre' => 'Nariño'],
        ['codigo' => '54', 'nombre' => 'Norte De Santander'],
        ['codigo' => '86', 'nombre' => 'Putumayo'],
        ['codigo' => '63', 'nombre' => 'Quindío'],
        ['codigo' => '66', 'nombre' => 'Risaralda'],
        ['codigo' => '88', 'nombre' => 'San Andrés Y Providencia'],
        ['codigo' => '68', 'nombre' => 'Santander'],
        ['codigo' => '70', 'nombre' => 'Sucre'],
        ['codigo' => '73', 'nombre' => 'Tolima'],
        ['codigo' => '76', 'nombre' => 'Valle Del Cauca'],
        ['codigo' => '97', 'nombre' => 'Vaupés'],
        ['codigo' => '99', 'nombre' => 'Vichada'],
    ];

    public function departamentos(): JsonResponse
    {
        $data = Cache::remember('divipola_departamentos', self::TTL, function () {
            try {
                $response = Http::timeout(15)->get(self::API_URL, [
                    '$select' => 'cod_dpto,dpto',
                    '$group'  => 'cod_dpto,dpto',
                    '$order'  => 'dpto ASC',
                    '$limit'  => '100',
                ]);

                if (!$response->successful()) {
                    return self::DEPARTAMENTOS_FALLBACK;
                }

                $result = collect($response->json())
                    ->filter(fn ($d) => !empty($d['dpto']))
                    ->map(fn ($d) => [
                        'codigo' => $d['cod_dpto'] ?? '',
                        'nombre' => mb_convert_case(strtolower($d['dpto']), MB_CASE_TITLE, 'UTF-8'),
                    ])
                    ->sortBy('nombre')
                    ->values()
                    ->toArray();

                return $result ?: self::DEPARTAMENTOS_FALLBACK;
            } catch (\Exception $e) {
                return self::DEPARTAMENTOS_FALLBACK;
            }
        });

        return response()->json($data);
    }

    public function municipios(string $codigo): JsonResponse
    {
        $codigo = preg_replace('/[^0-9]/', '', $codigo);

        $cacheKey = "divipola_municipios_{$codigo}";

        if ($cached = Cache::get($cacheKey)) {
            return response()->json($cached);
        }

        try {
            $response = Http::timeout(15)->get(self::API_URL, [
                '$where'  => "cod_dpto='{$codigo}'",
                '$select' => 'nom_mpio',
                '$order'  => 'nom_mpio ASC',
                '$limit'  => '500',
            ]);

            if (!$response->successful()) {
                return response()->json([]);
            }

            $data = collect($response->json())
                ->filter(fn ($m) => !empty($m['nom_mpio']))
                ->map(fn ($m) => mb_convert_case(strtolower($m['nom_mpio']), MB_CASE_TITLE, 'UTF-8'))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            if ($data) {
                Cache::put($cacheKey, $data, self::TTL);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}
