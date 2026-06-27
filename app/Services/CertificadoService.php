<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CertificadoService
{
    public function convertirPfxAPem(UploadedFile $pfxFile, string $password, Empresa $empresa): string
    {
        $pfxContent = file_get_contents($pfxFile->getRealPath());
        $certs = [];

        if (!openssl_pkcs12_read($pfxContent, $certs, $password)) {
            throw new \Exception('No se pudo leer el certificado PFX. Verifique la contraseña.');
        }

        $pem = ($certs['pkey'] ?? '') . ($certs['cert'] ?? '');
        if (!empty($certs['extracerts'])) {
            foreach ($certs['extracerts'] as $extra) {
                $pem .= $extra;
            }
        }

        $dir = "certificados/{$empresa->ruc}";
        $path = "{$dir}/certificado.pem";

        Storage::disk('local')->makeDirectory($dir);
        Storage::disk('local')->put($path, $pem);

        return $path;
    }
}
