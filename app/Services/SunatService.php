<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SunatService
{
    protected string $apiUrl;
    protected string $token;

    public function __construct()
    {
        $this->apiUrl = config('services.sunat.url', 'https://api.apis.net.pe/v1/ruc?token=') ;
        $this->token = config('services.sunat.token', '');
    }

    public function consultarRuc(string $ruc): array
    {
        if (strlen($ruc) !== 11 || !ctype_digit($ruc)) {
            return ['success' => false, 'message' => 'RUC debe tener 11 dígitos numéricos'];
        }

        return Cache::remember("sunat_ruc_{$ruc}", 604800, function () use ($ruc) {
            try {
                $response = Http::timeout(10)
                    ->get("{$this->apiUrl}/ruc/{$ruc}", [
                        'token' => $this->token,
                    ]);

                if ($response->successful() && isset($response['ruc'])) {
                    return [
                        'success' => true,
                        'data' => [
                            'ruc' => $response['ruc'],
                            'razon_social' => $response['razonSocial'] ?? '',
                            'nombre_comercial' => $response['nombreComercial'] ?? null,
                            'direccion' => $response['direccion'] ?? null,
                            'estado' => $response['estado'] ?? null,
                        ],
                    ];
                }

                return ['success' => false, 'message' => 'RUC no encontrado'];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Error al consultar SUNAT: ' . $e->getMessage()];
            }
        });
    }

    public function consultarDni(string $dni): array
    {
        if (strlen($dni) !== 8 || !ctype_digit($dni)) {
            return ['success' => false, 'message' => 'DNI debe tener 8 dígitos numéricos'];
        }

        return Cache::remember("sunat_dni_{$dni}", 604800, function () use ($dni) {
            try {
                $response = Http::timeout(10)
                    ->get("{$this->apiUrl}/dni/{$dni}", [
                        'token' => $this->token,
                    ]);

                if ($response->successful() && isset($response['dni'])) {
                    $nombre = trim(
                        ($response['nombres'] ?? '') . ' ' .
                        ($response['apellidoPaterno'] ?? '') . ' ' .
                        ($response['apellidoMaterno'] ?? '')
                    );

                    return [
                        'success' => true,
                        'data' => [
                            'dni' => $response['dni'],
                            'nombre' => $nombre,
                        ],
                    ];
                }

                return ['success' => false, 'message' => 'DNI no encontrado'];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Error al consultar RENIEC: ' . $e->getMessage()];
            }
        });
    }
}
