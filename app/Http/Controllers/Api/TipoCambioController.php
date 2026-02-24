<?php
// app/Http/Controllers/Api/TipoCambioController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TipoCambioController extends Controller
{
    /**
     * Obtener tipo de cambio del día (SUNAT)
     */
    public function obtener()
    {
        // Cachear por 6 horas
        $tipoCambio = Cache::remember('tipo_cambio', 3600 * 6, function () {
            try {
                // Intentar obtener de SUNAT
                $response = Http::get('https://api.apis.net.pe/v1/tipo-cambio-sunat');
                
                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'compra' => $data['compra'] ?? 3.70,
                        'venta' => $data['venta'] ?? 3.75,
                        'fecha' => $data['fecha'] ?? now()->format('Y-m-d')
                    ];
                }
            } catch (\Exception $e) {
                // Si falla, usar valores por defecto
                return [
                    'compra' => 3.70,
                    'venta' => 3.75,
                    'fecha' => now()->format('Y-m-d')
                ];
            }
        });

        return response()->json($tipoCambio);
    }
}