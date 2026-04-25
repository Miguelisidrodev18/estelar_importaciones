<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class DniLookupController extends Controller
{
    public function buscar(string $dni): JsonResponse
    {
        if (!preg_match('/^\d{8}$/', $dni)) {
            return response()->json(['error' => 'DNI debe tener 8 dígitos'], 422);
        }

        $token = config('services.reniec.token');

        // Intentar primero con token en v2 (si está configurado y no es el demo)
        if (!empty($token) && $token !== 'apis-token-demo') {
            try {
                $response = Http::withToken($token)
                    ->timeout(8)
                    ->get('https://api.apis.net.pe/v2/reniec/dni', ['numero' => $dni]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['nombre'])) {
                        return response()->json(['nombre' => $data['nombre']]);
                    }
                }

                if ($response->status() === 404) {
                    return response()->json(['error' => 'DNI no encontrado en RENIEC'], 404);
                }

                if ($response->status() === 401 || $response->status() === 403) {
                    // Token inválido, cae al fallback v1
                }
            } catch (\Exception) {
                // Cae al fallback v1
            }
        }

        // Fallback: endpoint v1 público (sin token, tasa limitada)
        try {
            $response = Http::timeout(8)
                ->get('https://api.apis.net.pe/v1/dni', ['numero' => $dni]);

            if ($response->successful()) {
                $data = $response->json();
                $nombre = $data['nombre']
                    ?? trim(($data['nombres'] ?? '') . ' ' . ($data['apellidoPaterno'] ?? '') . ' ' . ($data['apellidoMaterno'] ?? ''))
                    ?: null;

                if ($nombre) {
                    return response()->json(['nombre' => $nombre]);
                }
            }

            if ($response->status() === 404) {
                return response()->json(['error' => 'DNI no encontrado'], 404);
            }

            if ($response->status() === 429) {
                return response()->json(['error' => 'Límite de consultas alcanzado. Ingrese el nombre manualmente.'], 429);
            }

            return response()->json([
                'error' => 'No se pudo obtener el nombre. Ingrese el nombre manualmente.',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Sin conexión al servicio RENIEC. Ingrese el nombre manualmente.',
            ], 503);
        }
    }
}
